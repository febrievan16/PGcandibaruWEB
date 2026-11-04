<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\QuotaExceededException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class OrderApiController extends Controller
{
    public function __construct(
        public BookingService $bookingService,
    ) {}

    /**
     * POST /api/v1/orders
     * Create a new booking after selecting a schedule (DEC-003, DEC-004, API.md §3.2).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => ['required', 'integer', 'exists:schedules,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'customer.name' => ['required', 'string', 'max:150'],
            'customer.email' => ['required', 'email', 'max:150'],
            'customer.phone' => ['required', 'string', 'max:20'],
            'guest_checkout' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Data pemesanan tidak valid.',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        try {
            $order = $this->bookingService->createOrder([
                'schedule_id' => (int) $request->input('schedule_id'),
                'quantity' => (int) $request->input('quantity'),
                'buyer_name' => (string) $request->input('customer.name'),
                'buyer_email' => (string) $request->input('customer.email'),
                'buyer_phone' => (string) $request->input('customer.phone'),
                'user' => Auth::user(),
                'idempotency_key' => $request->header('X-Idempotency-Key'),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_code' => $order->order_code,
                    'access_token' => $order->access_token,
                    'status' => $order->status,
                    'quantity' => $order->ticket_qty,
                    'total_amount' => $order->total_amount,
                    'expires_at' => $order->expires_at?->toIso8601String(),
                    'payment' => [
                        'snap_token' => $order->snap_token,
                        'redirect_url' => $order->snap_redirect_url,
                    ],
                ],
            ], 201);
        } catch (QuotaExceededException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'QUOTA_EXCEEDED',
                    'message' => $e->getMessage(),
                    'details' => [
                        'remaining_quota' => $e->remainingQuota,
                    ],
                ],
            ], 409);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage(),
                    'details' => (object) [],
                ],
            ], 422);
        }
    }

    /**
     * GET /api/v1/orders/{order_code}
     * Retrieve order details & E-Ticket URLs (API.md §3.4).
     */
    public function show(Request $request, string $orderCode): JsonResponse
    {
        $order = Order::with(['schedule.event', 'tickets'])
            ->where('order_code', $orderCode)
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Pesanan tidak ditemukan.',
                    'details' => (object) [],
                ],
            ], 404);
        }

        $user = Auth::user();
        $token = (string) $request->query('token', '');
        $hasValidToken = ($token !== '' && hash_equals($order->access_token, $token));
        $isOwner = ($user !== null && ($user->id === $order->user_id || $user->isAdmin()));

        if (! $hasValidToken && ! $isOwner) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Anda tidak memiliki akses ke pesanan ini.',
                    'details' => (object) [],
                ],
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_code' => $order->order_code,
                'status' => $order->status,
                'status_label' => $order->statusLabel(),
                'quantity' => $order->ticket_qty,
                'total_amount' => $order->total_amount,
                'schedule' => [
                    'date' => $order->schedule->tour_date->format('Y-m-d'),
                    'session' => $order->schedule->formattedTimeRange(),
                ],
                'customer' => [
                    'name' => $order->buyer_name,
                    'email' => $order->buyer_email,
                ],
                'e_ticket' => $order->status === 'paid' ? [
                    'qr_url' => route('orders.ticket', ['order_code' => $order->order_code, 'token' => $order->access_token]),
                    'download_url' => route('orders.ticket', ['order_code' => $order->order_code, 'token' => $order->access_token, 'print' => 1]),
                    'tickets' => $order->tickets->map(fn ($t): array => [
                        'ticket_code' => $t->ticket_code,
                        'qr_payload' => $t->qr_payload,
                        'status' => $t->status,
                    ])->values(),
                ] : null,
            ],
        ]);
    }
}

