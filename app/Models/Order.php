<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_code',
    'idempotency_key',
    'access_token',
    'schedule_id',
    'user_id',
    'buyer_name',
    'buyer_email',
    'buyer_phone',
    'ticket_qty',
    'unit_price',
    'total_amount',
    'status',
    'midtrans_order_id',
    'midtrans_txn_id',
    'snap_token',
    'snap_redirect_url',
    'payment_channel',
    'paid_at',
    'expires_at',
])]
class Order extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ticket_qty' => 'integer',
            'unit_price' => 'integer',
            'total_amount' => 'integer',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the schedule associated with the order.
     *
     * @return BelongsTo<Schedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the registered user who placed the order (null for guests).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tickets generated for the order.
     *
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Explicit Indonesian status label per DESIGN.md §12.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'Lunas',
            'pending' => 'Menunggu Pembayaran',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Semantic color class for order status per DESIGN.md §4 & §12.
     */
    public function statusColorClass(): string
    {
        return match ($this->status) {
            'paid' => 'text-[#4A6B3A] border-[#4A6B3A] bg-[#4A6B3A]/10',
            'pending' => 'text-[#B8860B] border-[#B8860B] bg-[#B8860B]/10',
            'expired', 'cancelled' => 'text-[#8B2E1F] border-[#8B2E1F] bg-[#8B2E1F]/10',
            default => 'text-[#6E635A] border-[#E3D9C9]',
        };
    }

    /**
     * Format total amount in Rupiah.
     */
    public function formattedTotal(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }
}

