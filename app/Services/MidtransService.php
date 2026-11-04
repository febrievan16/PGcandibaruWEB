<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransService
{
    public function __construct(
        public TicketService $ticketService,
    ) {}

    /**
     * Create Midtrans Snap transaction data (or fallback to built-in demo simulator).
     *
     * @return array{snap_token: string, redirect_url: string}
     */
    public function createSnapTransaction(Order $order): array
    {
        $serverKey = (string) config('services.midtrans.server_key', '');
        $isProduction = (bool) config('services.midtrans.is_production', false);

        if ($serverKey !== '' && ! str_starts_with($serverKey, 'SB-Mid-server-GulaTourDemo')) {
            try {
                $endpoint = $isProduction
                    ? 'https://app.midtrans.com/snap/v1/transactions'
                    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

                $response = Http::withBasicAuth($serverKey, '')
                    ->timeout(6)
                    ->post($endpoint, [
                        'transaction_details' => [
                            'order_id' => $order->order_code,
                            'gross_amount' => $order->total_amount,
                        ],
                        'customer_details' => [
                            'first_name' => $order->buyer_name,
                            'email' => $order->buyer_email,
                            'phone' => $order->buyer_phone,
                        ],
                        'item_details' => [
                            [
                                'id' => 'SCH-' . $order->schedule_id,
                                'price' => $order->unit_price,
                                'quantity' => $order->ticket_qty,
                                'name' => substr($order->schedule->event->title ?? 'Tiket Tour PG Candi Baru', 0, 50),
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $snapToken = (string) ($json['token'] ?? Str::uuid());
                    $redirectUrl = (string) ($json['redirect_url'] ?? route('orders.show', ['order_code' => $order->order_code, 'token' => $order->access_token]));

                    $order->update([
                        'midtrans_order_id' => $order->order_code,
                        'snap_token' => $snapToken,
                        'snap_redirect_url' => $redirectUrl,
                    ]);

                    return [
                        'snap_token' => $snapToken,
                        'redirect_url' => $redirectUrl,
                    ];
                }
            } catch (\Throwable) {
                // Fallback to built-in sandbox simulator below.
            }
        }

        $snapToken = '66e4fa55-' . strtolower(Str::random(24));
        $redirectUrl = route('orders.show', [
            'order_code' => $order->order_code,
            'token' => $order->access_token,
        ]);

        $order->update([
            'midtrans_order_id' => $order->order_code,
            'snap_token' => $snapToken,
            'snap_redirect_url' => $redirectUrl,
        ]);

        return [
            'snap_token' => $snapToken,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Compute expected Midtrans SHA512 signature (`SHA512(order_id+status_code+gross_amount+server_key)`).
     */
    public function computeSignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        $serverKey = (string) config('services.midtrans.server_key', 'SB-Mid-server-GulaTourDemoKey');

        return hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
    }

    /**
     * Verify incoming Midtrans webhook signature.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expected = $this->computeSignature($orderId, $statusCode, $grossAmount);

        return hash_equals($expected, $signatureKey);
    }

    /**
     * Process Midtrans notification / payment update idempotently (FR-06, FR-07, FR-08).
     *
     * @param array{
     *     order_id: string,
     *     transaction_status: string,
     *     payment_type?: string,
     *     transaction_id?: string
     * } $payload
     */
    public function handleNotification(array $payload): Order
    {
        return DB::transaction(function () use ($payload): Order {
            /** @var Order $order */
            $order = Order::where('order_code', $payload['order_id'])
                ->orWhere('midtrans_order_id', $payload['order_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($order->status, ['paid', 'expired', 'cancelled'], true)) {
                return $order;
            }

            $txnStatus = strtolower($payload['transaction_status']);
            $paymentType = $payload['payment_type'] ?? 'bank_transfer';
            $txnId = $payload['transaction_id'] ?? ('MID-' . strtoupper(Str::random(10)));

            if (in_array($txnStatus, ['settlement', 'capture'], true)) {
                $order->update([
                    'status' => 'paid',
                    'payment_channel' => $paymentType,
                    'midtrans_txn_id' => $txnId,
                    'paid_at' => now(),
                ]);

                $this->ticketService->issueTicketsForOrder($order);

                AdminNotification::create([
                    'type' => 'payment_received',
                    'title' => "Pembayaran Masuk: {$order->order_code}",
                    'message' => "Pembayaran sebesar {$order->formattedTotal()} dari {$order->buyer_name} ({$order->ticket_qty} tiket) telah terverifikasi Lunas via {$paymentType}.",
                    'order_id' => $order->id,
                    'is_read' => false,
                ]);
            } elseif ($txnStatus === 'expire') {
                $this->releaseScheduleQuota($order);
                $order->update([
                    'status' => 'expired',
                    'payment_channel' => $paymentType,
                    'midtrans_txn_id' => $txnId,
                ]);
                $order->tickets()->update(['status' => 'void']);
            } elseif (in_array($txnStatus, ['cancel', 'deny'], true)) {
                $this->releaseScheduleQuota($order);
                $order->update([
                    'status' => 'cancelled',
                    'payment_channel' => $paymentType,
                    'midtrans_txn_id' => $txnId,
                ]);
                $order->tickets()->update(['status' => 'void']);
            }

            return $order->fresh(['tickets', 'schedule.event']);
        });
    }

    /**
     * Release locked quota when a pending order expires or is cancelled.
     */
    public function releaseScheduleQuota(Order $order): void
    {
        if ($order->status !== 'pending') {
            return;
        }

        /** @var Schedule|null $schedule */
        $schedule = Schedule::whereKey($order->schedule_id)->lockForUpdate()->first();
        if (! $schedule) {
            return;
        }

        $newBooked = max(0, $schedule->quota_booked - $order->ticket_qty);
        $newStatus = $schedule->status;
        if ($newStatus === 'full' && $newBooked < $schedule->quota_total) {
            $newStatus = 'open';
        }

        $schedule->update([
            'quota_booked' => $newBooked,
            'status' => $newStatus,
        ]);
    }
}

