<?php

namespace App\Services;

use App\Exceptions\QuotaExceededException;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        public MidtransService $midtransService,
    ) {}

    /**
     * Create a new booking with atomic quota locking and server-side flat pricing (DEC-001, DEC-003, DEC-004).
     *
     * @param array{
     *     schedule_id: int,
     *     quantity: int,
     *     buyer_name: string,
     *     buyer_email: string,
     *     buyer_phone: string,
     *     user?: ?User,
     *     idempotency_key?: ?string
     * } $data
     */
    public function createOrder(array $data): Order
    {
        $idempotencyKey = ! empty($data['idempotency_key']) ? trim((string) $data['idempotency_key']) : null;

        if ($idempotencyKey !== null) {
            $existingOrder = Order::where('idempotency_key', $idempotencyKey)->first();
            if ($existingOrder !== null) {
                return $existingOrder->load(['schedule.event', 'tickets']);
            }
        }

        return DB::transaction(function () use ($data, $idempotencyKey): Order {
            /** @var Schedule $schedule */
            $schedule = Schedule::with('event')
                ->whereKey($data['schedule_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $event = $schedule->event;
            if (! $event || $event->status !== 'active' || $event->ticket_price <= 0) {
                throw new InvalidArgumentException('Event tour ini belum aktif atau harga tiket belum dikonfigurasi.');
            }

            if ($schedule->isPast() || $schedule->status === 'closed') {
                throw new QuotaExceededException('Sesi jadwal yang dipilih sudah ditutup atau telah lewat.', 0);
            }

            $qty = (int) $data['quantity'];
            $remaining = $schedule->remainingQuota();

            if ($qty < 1 || $qty > $remaining || $schedule->status === 'full') {
                throw new QuotaExceededException(
                    "Kuota sesi tidak mencukupi. Sisa kuota saat ini: {$remaining} tiket.",
                    $remaining
                );
            }

            // Server-side flat price calculation per DEC-001
            $unitPrice = (int) $event->ticket_price;
            $totalAmount = $unitPrice * $qty;

            $newBooked = $schedule->quota_booked + $qty;
            $newScheduleStatus = ($newBooked >= $schedule->quota_total) ? 'full' : 'open';

            $schedule->update([
                'quota_booked' => $newBooked,
                'status' => $newScheduleStatus,
            ]);

            $datePart = $schedule->tour_date->format('Ymd');
            $orderCode = $this->generateUniqueOrderCode($datePart);
            $expiryMinutes = (int) config('services.midtrans.order_expiry_minutes', 15);

            $order = Order::create([
                'order_code' => $orderCode,
                'idempotency_key' => $idempotencyKey,
                'access_token' => Str::random(40),
                'schedule_id' => $schedule->id,
                'user_id' => $data['user']?->id,
                'buyer_name' => $data['buyer_name'],
                'buyer_email' => $data['buyer_email'],
                'buyer_phone' => $data['buyer_phone'],
                'ticket_qty' => $qty,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'expires_at' => now()->addMinutes($expiryMinutes),
            ]);

            $this->midtransService->createSnapTransaction($order);

            AdminNotification::create([
                'type' => 'new_order',
                'title' => "Pesanan Baru: {$order->order_code}",
                'message' => "{$order->buyer_name} memesan {$order->ticket_qty} tiket untuk {$event->title} ({$schedule->tour_date->format('d M Y')} - {$schedule->session_code}).",
                'order_id' => $order->id,
                'is_read' => false,
            ]);

            return $order->fresh(['schedule.event', 'tickets']);
        });
    }

    /**
     * Generate a unique order code (`GT-YYYYMMDD-XXXXXX`).
     */
    protected function generateUniqueOrderCode(string $datePart): string
    {
        do {
            $suffix = strtoupper(Str::random(6));
            $code = "GT-{$datePart}-{$suffix}";
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}

