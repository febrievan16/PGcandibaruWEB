<?php

namespace App\Http\Controllers;

use App\Exceptions\QuotaExceededException;
use App\Models\Event;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

class EventBookingController extends Controller
{
    public function __construct(
        public BookingService $bookingService,
    ) {}

    /**
     * Display active tour events catalog (M2: FR-02).
     */
    public function index(): View
    {
        $events = Event::with('schedules')
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        return view('events.index', [
            'events' => $events,
        ]);
    }

    /**
     * Display event details and the 2-Step Schedule-First Booking Wizard (M2 & M3: DEC-001, DEC-003).
     */
    public function show(Request $request, string $slug): View
    {
        $event = Event::with('schedules')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $selectedScheduleId = $request->integer('schedule_id') ?: null;
        $selectedSchedule = null;

        if ($selectedScheduleId !== null) {
            $candidate = $event->schedules->firstWhere('id', $selectedScheduleId);
            if ($candidate && $candidate->isAvailable()) {
                $selectedSchedule = $candidate;
            }
        }

        return view('events.show', [
            'event' => $event,
            'schedulesByDate' => $event->schedules->groupBy(fn ($s): string => $s->tour_date->format('Y-m-d')),
            'selectedSchedule' => $selectedSchedule,
        ]);
    }

    /**
     * Process Step 2 of the Booking Wizard (M3: DEC-001, DEC-003, DEC-004).
     */
    public function store(Request $request, string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'schedule_id' => ['required', 'integer', 'exists:schedules,id'],
            'ticket_qty' => ['required', 'integer', 'min:1', 'max:40'],
            'buyer_name' => ['required', 'string', 'max:150'],
            'buyer_email' => ['required', 'email', 'max:150'],
            'buyer_phone' => ['required', 'string', 'max:20'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ], [
            'schedule_id.required' => 'Pilih tanggal dan sesi kunjungan terlebih dahulu (Langkah 1).',
            'ticket_qty.min' => 'Jumlah tiket minimal 1.',
            'buyer_name.required' => 'Nama lengkap pemesan wajib diisi.',
            'buyer_email.required' => 'Alamat email aktif wajib diisi untuk pengiriman E-Ticket.',
            'buyer_phone.required' => 'Nomor WhatsApp wajib diisi untuk pengiriman E-Ticket.',
        ]);

        try {
            $order = $this->bookingService->createOrder([
                'schedule_id' => (int) $validated['schedule_id'],
                'quantity' => (int) $validated['ticket_qty'],
                'buyer_name' => $validated['buyer_name'],
                'buyer_email' => $validated['buyer_email'],
                'buyer_phone' => $validated['buyer_phone'],
                'user' => Auth::user(),
                'idempotency_key' => $validated['idempotency_key'] ?? null,
            ]);

            return redirect()->route('orders.show', [
                'order_code' => $order->order_code,
                'token' => $order->access_token,
            ])->with('status', 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran sebelum batas waktu berakhir.');
        } catch (QuotaExceededException|InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['booking' => $e->getMessage()]);
        }
    }
}

