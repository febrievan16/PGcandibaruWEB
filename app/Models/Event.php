<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug',
    'title',
    'description',
    'highlights',
    'duration_minutes',
    'meeting_point',
    'cover_image',
    'ticket_price',
    'status',
])]
class Event extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'duration_minutes' => 'integer',
            'ticket_price' => 'integer',
        ];
    }

    /**
     * Get all schedules for the event.
     *
     * @return HasMany<Schedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class)->orderBy('tour_date')->orderBy('start_time');
    }

    /**
     * Determine whether the event meets FR-04 requirements to be activated.
     */
    public function canBeActivated(): bool
    {
        if ($this->ticket_price <= 0) {
            return false;
        }

        return $this->schedules()
            ->where('quota_total', '>', 0)
            ->exists();
    }

    /**
     * Format ticket price in Rupiah.
     */
    public function formattedPrice(): string
    {
        if ($this->ticket_price <= 0) {
            return 'TBD (Belum Dikonfigurasi)';
        }

        return 'Rp ' . number_format($this->ticket_price, 0, ',', '.');
    }
}

