<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;

class EventScheduleApiController extends Controller
{
    /**
     * GET /api/v1/events/{event_slug}/schedules
     * Retrieve active event schedules and real-time remaining quotas (FR-02, FR-09, API.md §3.1).
     */
    public function show(string $eventSlug): JsonResponse
    {
        $event = Event::with('schedules')
            ->where('slug', $eventSlug)
            ->where('status', 'active')
            ->first();

        if (! $event) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Event tour tidak ditemukan atau belum aktif.',
                    'details' => (object) [],
                ],
            ], 404);
        }

        $schedules = $event->schedules->map(function (Schedule $schedule): array {
            return [
                'schedule_id' => $schedule->id,
                'date' => $schedule->tour_date->format('Y-m-d'),
                'session_code' => $schedule->session_code,
                'session' => $schedule->formattedTimeRange(),
                'quota' => $schedule->quota_total,
                'booked' => $schedule->quota_booked,
                'remaining' => $schedule->remainingQuota(),
                'is_available' => $schedule->isAvailable(),
                'status_label' => $schedule->statusLabel(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'event_id' => $event->id,
                'slug' => $event->slug,
                'title' => $event->title,
                'ticket_price' => $event->ticket_price,
                'currency' => 'IDR',
                'schedules' => $schedules,
            ],
        ]);
    }
}

