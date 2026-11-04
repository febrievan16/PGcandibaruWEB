<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Event;
use App\Models\GalleryItem;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Render the Heritage Editorial landing page (M1 + M2 preview).
     */
    public function home(): View
    {
        $profile = CompanyProfile::first();
        $galleryItems = GalleryItem::where('is_active', true)
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $events = Event::with(['schedules' => function ($query): void {
            $query->whereDate('tour_date', '>=', today()->toDateString())
                ->orderBy('tour_date')
                ->orderBy('start_time');
        }])
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('pages.home', [
            'profile' => $profile,
            'galleryItems' => $galleryItems,
            'events' => $events,
        ]);
    }

    /**
     * Render the dedicated Company Profile page (M1: FR-01, FR-12).
     */
    public function profile(): View
    {
        $profile = CompanyProfile::first();
        $galleryItems = GalleryItem::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('pages.profile', [
            'profile' => $profile,
            'galleryItems' => $galleryItems,
        ]);
    }
}

