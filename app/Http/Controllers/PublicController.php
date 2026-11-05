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
     * Summary columns for article listings (avoids fetching longText `content` on index views).
     *
     * @var list<string>
     */
    protected const ARTICLE_LIST_COLUMNS = [
        'id',
        'slug',
        'title',
        'category',
        'author_name',
        'excerpt',
        'cover_image',
        'reading_time_minutes',
        'is_published',
        'published_at',
        'created_at',
    ];

    /**
     * Render the Heritage Editorial landing page (M1 + M2 preview).
     */
    public function home(): View
    {
        $profile = CompanyProfile::current();

        $events = Event::with(['schedules' => function ($query): void {
            $query->whereDate('tour_date', '>=', today()->toDateString())
                ->orderBy('tour_date')
                ->orderBy('start_time');
        }])
            ->where('status', 'active')
            ->latest('id')
            ->get();

        return view('pages.home', [
            'profile' => $profile,
            'events' => $events,
        ]);
    }

    /**
     * Render the dedicated Company Profile page including Gallery and Heritage Articles (M1: FR-01, FR-12).
     */
    public function profile(): View
    {
        $profile = CompanyProfile::current();
        $galleryItems = GalleryItem::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $articlesQuery = Article::query()
            ->select(self::ARTICLE_LIST_COLUMNS)
            ->latest('published_at')
            ->latest('id');

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
        $query = Article::query()
            ->select(self::ARTICLE_LIST_COLUMNS)
            ->latest('published_at')
            ->latest('id');

        if (! Auth::user()?->isAdmin()) {
            $query->where('is_published', true);
        }

        return view('articles.index', [
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

        $relatedArticles = Article::query()
            ->select(['id', 'slug', 'title', 'category', 'published_at'])
            ->where('is_published', true)
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
