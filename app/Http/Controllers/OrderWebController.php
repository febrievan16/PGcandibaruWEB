<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderWebController extends Controller
{
    public function __construct(
        public MidtransService $midtransService,
        public TicketService $ticketService,
    ) {}

    /**
     * Render guest order lookup form.
     */
    public function lookupForm(): View
    {
        return view('orders.lookup');
    }

    /**
     * Find order by code and buyer email, redirecting to signed order URL.
     */
    public function lookupSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_code' => ['required', 'string', 'max:40'],
            'buyer_email' => ['required', 'email', 'max:150'],
        ]);

        $order = Order::where('order_code', trim($validated['order_code']))
            ->whereRaw('LOWER(buyer_email) = ?', [strtolower(trim($validated['buyer_email']))])
            ->first();

        if (! $order) {
            return back()
                ->withInput()
                ->withErrors(['lookup' => 'Pesanan dengan kombinasi Kode Order dan Email tersebut tidak ditemukan.']);
        }

        return redirect()->route('orders.show', [
            'order_code' => $order->order_code,
            'token' => $order->access_token,
        ]);
    }

    /**
     * Display Order Checkout, Payment Status, and E-Ticket overview (M4: FR-06..08).
     */
    public function show(Request $request, string $orderCode): View
    {
        $order = Order::with(['schedule.event', 'tickets'])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        $this->authorizeOrderAccess($request, $order);

        return view('orders.show', [
            'order' => $order,
            'ticketService' => $this->ticketService,
        ]);
    }

    /**
     * Built-in Midtrans Sandbox / Payment Simulator for instant testing of settlement, expiry, or cancellation.
     */
    public function simulatePayment(Request $request, string $orderCode): RedirectResponse
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        $this->authorizeOrderAccess($request, $order);

        $validated = $request->validate([
            'transaction_status' => ['required', 'in:settlement,expire,cancel'],
            'payment_type' => ['nullable', 'string', 'max:40'],
        ]);

        $updatedOrder = $this->midtransService->handleNotification([
            'order_id' => $order->order_code,
            'transaction_status' => $validated['transaction_status'],
            'payment_type' => $validated['payment_type'] ?? 'bank_transfer_bca',
        ]);

        $message = match ($updatedOrder->status) {
            'paid' => 'Pembayaran terverifikasi Lunas! E-Ticket ber-QR telah diterbitkan dan siap diunduh.',
            'expired' => 'Status pesanan diperbarui menjadi Kedaluwarsa dan kuota sesi telah dikembalikan.',
            'cancelled' => 'Pesanan telah Dibatalkan dan kuota sesi telah dikembalikan.',
            default => 'Status pembayaran diperbarui.',
        };

        return redirect()->route('orders.show', [
            'order_code' => $order->order_code,
            'token' => $order->access_token,
        ])->with('status', $message);
    }

    /**
     * Render printable / downloadable QR E-Ticket page (FR-07, FR-10).
     */
    public function ticket(Request $request, string $orderCode): View
    {
        $order = Order::with(['schedule.event', 'tickets'])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        $this->authorizeOrderAccess($request, $order);

        if ($order->status !== 'paid') {
            abort(403, 'E-Ticket hanya tersedia untuk pesanan yang telah Lunas.');
        }

        return view('orders.ticket-print', [
            'order' => $order,
            'ticketService' => $this->ticketService,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    /**
     * Verify guest token or authenticated ownership/admin role.
     */
    protected function authorizeOrderAccess(Request $request, Order $order): void
    {
        $token = (string) ($request->query('token') ?? $request->input('token') ?? '');
        $user = Auth::user();

        $hasValidToken = ($token !== '' && hash_equals($order->access_token, $token));
        $isOwnerOrAdmin = ($user !== null && ($user->id === $order->user_id || $user->isAdmin()));

        if (! $hasValidToken && ! $isOwnerOrAdmin) {
            abort(403, 'Akses E-Ticket memerlukan tautan bertanda tangan (token) yang sah atau akun pemilik pesanan.');
        }
    }
}

