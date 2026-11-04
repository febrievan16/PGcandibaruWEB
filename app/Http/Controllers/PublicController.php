<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\CompanyProfile;
use App\Models\Event;
use App\Models\GalleryItem;
use Illuminate\Support\Facades\Auth;
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
     * Render the dedicated Company Profile page including Gallery and Heritage Articles (M1: FR-01, FR-12).
     */
    public function profile(): View
    {
        $profile = CompanyProfile::first();
        $galleryItems = GalleryItem::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $articlesQuery = Article::latest('published_at')->latest('id');
        if (! Auth::user()?->isAdmin()) {
            $articlesQuery->where('is_published', true);
        }
        $articles = $articlesQuery->get();

        return view('pages.profile', [
            'profile' => $profile,
            'galleryItems' => $galleryItems,
            'articles' => $articles,
        ]);
    }

    /**
     * Render the full Heritage Articles index page.
     */
    public function articles(): View
    {
        $profile = CompanyProfile::first();
        $query = Article::latest('published_at')->latest('id');

        if (! Auth::user()?->isAdmin()) {
            $query->where('is_published', true);
        }

        return view('articles.index', [
            'profile' => $profile,
            'articles' => $query->get(),
        ]);
    }

    /**
     * Render a single Heritage Article reading page.
     */
    public function showArticle(string $slug): View
    {
        $query = Article::where('slug', $slug);
        if (! Auth::user()?->isAdmin()) {
            $query->where('is_published', true);
        }

        $article = $query->firstOrFail();

        $relatedArticles = Article::where('is_published', true)
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('articles.show', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}
