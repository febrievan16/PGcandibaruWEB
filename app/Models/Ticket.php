<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'ticket_code',
    'qr_payload',
    'status',
    'checked_in_at',
])]
class Ticket extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    /**
     * Get the order that owns the ticket.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Human-readable ticket status label in Indonesian.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'valid' => 'Berlaku (Belum Check-In)',
            'used' => 'Sudah Digunakan (Checked-In)',
            'void' => 'Dibatalkan (Void)',
            default => ucfirst($this->status),
        };
    }
}

