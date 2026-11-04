<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'event_id',
    'tour_date',
    'session_code',
    'start_time',
    'end_time',
    'quota_total',
    'quota_booked',
    'status',
])]
class Schedule extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tour_date' => 'date',
            'quota_total' => 'integer',
            'quota_booked' => 'integer',
        ];
    }

    /**
     * Get the event that owns the schedule.
     *
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the orders for this schedule.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Calculate remaining quota (`quota_total - quota_booked`).
     */
    public function remainingQuota(): int
    {
        return max(0, $this->quota_total - $this->quota_booked);
    }

    /**
     * Determine whether this schedule date is in the past.
     */
    public function isPast(): bool
    {
        return Carbon::parse($this->tour_date)->startOfDay()->lt(Carbon::today());
    }

    /**
     * Determine whether this schedule is available for booking.
     */
    public function isAvailable(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        if ($this->isPast()) {
            return false;
        }

        return $this->remainingQuota() > 0;
    }

    /**
     * Format session time range (`HH:MM-HH:MM`).
     */
    public function formattedTimeRange(): string
    {
        $start = substr((string) $this->start_time, 0, 5);
        $end = substr((string) $this->end_time, 0, 5);

        return "{$start}-{$end}";
    }

    /**
     * Human-readable status label in Indonesian per DESIGN.md.
     */
    public function statusLabel(): string
    {
        if ($this->isPast()) {
            return 'Sesi Berakhir';
        }

        if ($this->remainingQuota() <= 0 || $this->status === 'full') {
            return 'Kuota Penuh';
        }

        if ($this->status === 'closed') {
            return 'Ditutup';
        }

        return 'Tersedia';
    }
}

