<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminApiController extends Controller
{
    public function __construct(
        public TicketService $ticketService,
    ) {}

    /**
     * GET /api/v1/admin/orders
     * List orders with filtering, pagination, and CSV export (FR-14, FR-15, API.md §3.5).
     */
    public function orders(Request $request): JsonResponse|StreamedResponse
    {
        $query = Order::with(['schedule.event'])
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', (int) $request->query('schedule_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', (string) $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', (string) $request->query('to'));
        }

        if ($request->query('export') === 'csv') {
            $orders = $query->get();
            $filename = 'laporan-pemesanan-gulatour-' . now()->format('Ymd-His') . '.csv';

            return response()->streamDownload(function () use ($orders): void {
                $handle = fopen('php://output', 'w');
                if ($handle === false) {
                    return;
                }

                fputcsv($handle, [
                    'Order Code',
                    'Nama Pemesan',
                    'Email',
                    'Telepon',
                    'Event',
                    'Tanggal Tour',
                    'Sesi',
                    'Jumlah Tiket',
                    'Total (IDR)',
                    'Status',
                    'Waktu Lunas',
                ]);

                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->order_code,
                        $order->buyer_name,
                        $order->buyer_email,
                        $order->buyer_phone,
                        $order->schedule->event->title ?? '-',
                        $order->schedule->tour_date->format('Y-m-d'),
                        $order->schedule->formattedTimeRange(),
                        $order->ticket_qty,
                        $order->total_amount,
                        $order->status,
                        $order->paid_at?->toIso8601String() ?? '-',
                    ]);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(function (Order $order): array {
            return [
                'order_code' => $order->order_code,
                'customer_name' => $order->buyer_name,
                'schedule_date' => $order->schedule->tour_date->format('Y-m-d'),
                'session' => $order->schedule->formattedTimeRange(),
                'quantity' => $order->ticket_qty,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'paid_at' => $order->paid_at?->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/tickets/validate
     * Validate QR payload or ticket code and mark ticket as used (FR-14, TASK-13).
     */
    public function validateTicket(Request $request): JsonResponse
    {
        $code = (string) ($request->input('qr_payload') ?? $request->input('ticket_code') ?? '');

        if (trim($code) === '') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Kode tiket atau QR payload wajib diisi.',
                    'details' => (object) [],
                ],
            ], 422);
        }

        $result = $this->ticketService->validateAndCheckIn($code);

        if (! $result['valid']) {
            $status = $result['code'] === 'TICKET_NOT_FOUND' ? 404 : 409;

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $result['code'],
                    'message' => $result['message'],
                    'details' => $result['ticket'] ? [
                        'ticket_code' => $result['ticket']->ticket_code,
                        'status' => $result['ticket']->status,
                        'checked_in_at' => $result['ticket']->checked_in_at?->toIso8601String(),
                    ] : (object) [],
                ],
            ], $status);
        }

        $ticket = $result['ticket'];

        return response()->json([
            'success' => true,
            'data' => [
                'message' => $result['message'],
                'ticket_code' => $ticket?->ticket_code,
                'status' => $ticket?->status,
                'checked_in_at' => $ticket?->checked_in_at?->toIso8601String(),
                'order_code' => $ticket?->order->order_code,
                'buyer_name' => $ticket?->order->buyer_name,
                'event_title' => $ticket?->order->schedule->event->title ?? '-',
                'tour_date' => $ticket?->order->schedule->tour_date->format('Y-m-d'),
                'session' => $ticket?->order->schedule->formattedTimeRange(),
            ],
        ]);
    }
}

