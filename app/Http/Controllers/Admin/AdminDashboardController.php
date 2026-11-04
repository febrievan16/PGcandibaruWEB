<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\CompanyProfile;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Schedule;
use App\Services\MidtransService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        public TicketService $ticketService,
        public MidtransService $midtransService,
    ) {}

    /**
     * Render Admin Dashboard overview: revenue, order metrics, session occupancy, notifications, audit log (FR-11, FR-16).
     */
    public function index(): View
    {
        $totalRevenue = (int) Order::where('status', 'paid')->sum('total_amount');
        $paidOrdersCount = Order::where('status', 'paid')->count();
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $ticketsSoldCount = (int) Order::where('status', 'paid')->sum('ticket_qty');

        $schedules = Schedule::with('event')
            ->orderBy('tour_date')
            ->orderBy('start_time')
            ->get();

        $notifications = AdminNotification::with('order')
            ->latest('created_at')
            ->limit(8)
            ->get();

        $unreadNotificationsCount = AdminNotification::where('is_read', false)->count();

        $auditLogs = AuditLog::with('user')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'totalRevenue' => $totalRevenue,
            'paidOrdersCount' => $paidOrdersCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'ticketsSoldCount' => $ticketsSoldCount,
            'schedules' => $schedules,
            'notifications' => $notifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Master-Detail Orders view with filters (DESIGN.md §6, FR-14).
     */
    public function orders(Request $request): View
    {
        $query = Order::with(['schedule.event', 'tickets', 'user'])
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', (int) $request->query('schedule_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%")
                    ->orWhere('buyer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        $selectedOrder = null;
        if ($request->filled('inspect')) {
            $selectedOrder = Order::with(['schedule.event', 'tickets', 'user'])
                ->where('order_code', (string) $request->query('inspect'))
                ->first();
        }
        if (! $selectedOrder && $orders->count() > 0) {
            $selectedOrder = $orders->first();
        }

        $schedules = Schedule::with('event')->orderByDesc('tour_date')->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'selectedOrder' => $selectedOrder,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Manual order status update by Admin with audit logging (FR-14).
     */
    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,expired,cancelled'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            if ($newStatus === 'paid') {
                $this->midtransService->handleNotification([
                    'order_id' => $order->order_code,
                    'transaction_status' => 'settlement',
                    'payment_type' => 'manual_admin_verification',
                ]);
            } elseif (in_array($newStatus, ['expired', 'cancelled'], true)) {
                $this->midtransService->releaseScheduleQuota($order);
                $order->update(['status' => $newStatus]);
                $order->tickets()->update(['status' => 'void']);
            } else {
                $order->update(['status' => $newStatus]);
            }

            $this->recordAudit('order.status_updated', 'Order', $order->id, [
                'status' => $oldStatus,
            ], [
                'status' => $newStatus,
            ], $request);
        }

        return back()->with('status', "Status pesanan {$order->order_code} berhasil diperbarui menjadi {$order->fresh()->statusLabel()}.");
    }

    /**
     * List all events & schedules for Admin management (FR-13).
     */
    public function events(): View
    {
        $events = Event::withCount('schedules')
            ->with('schedules')
            ->orderByDesc('id')
            ->get();

        return view('admin.events.index', [
            'events' => $events,
        ]);
    }

    /**
     * Show the 3-Tab Event Editor (Info, Jadwal, Harga/Kuota) per DESIGN.md §6.
     */
    public function editEvent(Request $request, ?Event $event = null): View
    {
        if ($event && $event->exists) {
            $event->load('schedules');
        }

        return view('admin.events.form', [
            'event' => $event,
            'activeTab' => (string) $request->query('tab', 'info'),
        ]);
    }

    /**
     * Create or update an Event, enforcing FR-04 before allowing `status = active`.
     */
    public function saveEvent(Request $request, ?Event $event = null): RedirectResponse
    {
        $isUpdate = ($event !== null && $event->exists);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:160'],
            'description' => ['required', 'string'],
            'highlights_text' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:600'],
            'meeting_point' => ['required', 'string', 'max:200'],
            'ticket_price' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,active,inactive'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $slugQuery = Event::where('slug', $slug);
        if ($isUpdate) {
            $slugQuery->where('id', '!=', $event->id);
        }
        if ($slugQuery->exists()) {
            $slug .= '-' . strtolower(Str::random(4));
        }

        $highlights = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['highlights_text'] ?? '')))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();

        // Enforce FR-04 & TASK-12: Event cannot be activated if ticket_price <= 0 or no schedules with quota > 0 exist
        if ($validated['status'] === 'active') {
            $hasValidPrice = ((int) $validated['ticket_price'] > 0);
            $hasValidSchedule = $isUpdate
                && $event->schedules()->where('quota_total', '>', 0)->exists();

            if (! $hasValidPrice || ! $hasValidSchedule) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'status' => 'Event tidak dapat diaktifkan (FR-04): Harga tiket tetap wajib lebih dari Rp 0 dan minimal harus memiliki 1 sesi jadwal dengan kuota > 0.',
                    ]);
            }
        }

        if ($isUpdate) {
            $oldValues = $event->only(['title', 'ticket_price', 'status']);
            $event->update([
                'title' => $validated['title'],
                'slug' => $slug,
                'description' => $validated['description'],
                'highlights' => $highlights,
                'duration_minutes' => (int) $validated['duration_minutes'],
                'meeting_point' => $validated['meeting_point'],
                'ticket_price' => (int) $validated['ticket_price'],
                'status' => $validated['status'],
            ]);

            $this->recordAudit('event.updated', 'Event', $event->id, $oldValues, [
                'title' => $event->title,
                'ticket_price' => $event->ticket_price,
                'status' => $event->status,
            ], $request);

            return redirect()->route('admin.events.edit', ['event' => $event->id, 'tab' => $request->input('return_tab', 'info')])
                ->with('status', 'Data event berhasil diperbarui dan tercatat di Audit Log.');
        }

        $newEvent = Event::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'highlights' => $highlights,
            'duration_minutes' => (int) $validated['duration_minutes'],
            'meeting_point' => $validated['meeting_point'],
            'ticket_price' => (int) $validated['ticket_price'],
            'status' => $validated['status'],
        ]);

        $this->recordAudit('event.created', 'Event', $newEvent->id, null, [
            'title' => $newEvent->title,
            'ticket_price' => $newEvent->ticket_price,
            'status' => $newEvent->status,
        ], $request);

        return redirect()->route('admin.events.edit', ['event' => $newEvent->id, 'tab' => 'schedules'])
            ->with('status', 'Event baru berhasil dibuat. Silakan tambahkan jadwal sesi dan kuota.');
    }

    /**
     * Add a new schedule session to an event (FR-13).
     */
    public function storeSchedule(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'tour_date' => ['required', 'date'],
            'session_code' => ['required', 'string', 'max:20'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'quota_total' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:open,closed'],
        ]);

        $sessionCode = strtoupper(trim($validated['session_code']));

        $exists = $event->schedules()
            ->whereDate('tour_date', $validated['tour_date'])
            ->where('session_code', $sessionCode)
            ->exists();

        if ($exists) {
            return redirect()->route('admin.events.edit', ['event' => $event->id, 'tab' => 'schedules'])
                ->withErrors(['schedule' => "Sesi {$sessionCode} pada tanggal {$validated['tour_date']} sudah terdaftar."]);
        }

        $schedule = $event->schedules()->create([
            'tour_date' => $validated['tour_date'],
            'session_code' => $sessionCode,
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $validated['end_time'] . ':00',
            'quota_total' => (int) $validated['quota_total'],
            'quota_booked' => 0,
            'status' => $validated['status'],
        ]);

        $this->recordAudit('schedule.created', 'Schedule', $schedule->id, null, [
            'event_id' => $event->id,
            'tour_date' => $validated['tour_date'],
            'session_code' => $sessionCode,
            'quota_total' => (int) $validated['quota_total'],
        ], $request);

        return redirect()->route('admin.events.edit', ['event' => $event->id, 'tab' => 'schedules'])
            ->with('status', "Sesi {$sessionCode} ({$validated['tour_date']}) berhasil ditambahkan.");
    }

    /**
     * Update an existing schedule's quota or status, enforcing quota_total >= quota_booked (FEATURES.md §7).
     */
    public function updateSchedule(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'quota_total' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:open,closed,full'],
        ]);

        $newQuotaTotal = (int) $validated['quota_total'];

        if ($newQuotaTotal < $schedule->quota_booked) {
            return redirect()->route('admin.events.edit', ['event' => $schedule->event_id, 'tab' => 'schedules'])
                ->withErrors([
                    'schedule' => "Kuota total ({$newQuotaTotal}) tidak boleh lebih kecil dari jumlah tiket yang sudah dipesan ({$schedule->quota_booked}).",
                ]);
        }

        $oldValues = $schedule->only(['quota_total', 'status']);
        $newStatus = $validated['status'];
        if ($newQuotaTotal === $schedule->quota_booked && $newStatus === 'open') {
            $newStatus = 'full';
        } elseif ($newQuotaTotal > $schedule->quota_booked && $newStatus === 'full') {
            $newStatus = 'open';
        }

        $schedule->update([
            'quota_total' => $newQuotaTotal,
            'status' => $newStatus,
        ]);

        $this->recordAudit('schedule.updated', 'Schedule', $schedule->id, $oldValues, [
            'quota_total' => $schedule->quota_total,
            'status' => $schedule->status,
        ], $request);

        return redirect()->route('admin.events.edit', ['event' => $schedule->event_id, 'tab' => 'schedules'])
            ->with('status', 'Kuota dan status sesi berhasil diperbarui.');
    }

    /**
     * Render QR Ticket Scanner & Check-In Validation page (FR-14).
     */
    public function scanner(): View
    {
        $recentCheckIns = \App\Models\Ticket::with(['order.schedule.event'])
            ->where('status', 'used')
            ->latest('checked_in_at')
            ->limit(10)
            ->get();

        return view('admin.tickets.scanner', [
            'recentCheckIns' => $recentCheckIns,
        ]);
    }

    /**
     * Handle web form submission for QR ticket check-in (FR-14).
     */
    public function validateTicketWeb(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->ticketService->validateAndCheckIn($validated['code']);
        $ticket = $result['ticket'];

        $flashPayload = [
            'valid' => $result['valid'],
            'code' => $result['code'],
            'message' => $result['message'],
            'ticket' => $ticket ? [
                'ticket_code' => $ticket->ticket_code,
                'buyer_name' => $ticket->order->buyer_name ?? '-',
                'order_code' => $ticket->order->order_code ?? '-',
                'event_title' => $ticket->order->schedule->event->title ?? '-',
            ] : null,
        ];

        if (! $result['valid']) {
            return back()
                ->withInput()
                ->with('scan_result', $flashPayload);
        }

        $this->recordAudit('ticket.checked_in', 'Ticket', (int) $ticket?->id, [
            'status' => 'valid',
        ], [
            'status' => 'used',
            'ticket_code' => $ticket?->ticket_code,
        ], $request);

        return back()->with('scan_result', $flashPayload);
    }

    /**
     * Edit Company Profile & Gallery items (FR-12).
     */
    public function editProfile(): View
    {
        $profile = CompanyProfile::first();
        $galleryItems = GalleryItem::orderBy('sort_order')->get();
        $articles = Article::latest('published_at')->latest('id')->get();

        return view('admin.profile.edit', [
            'profile' => $profile,
            'galleryItems' => $galleryItems,
            'articles' => $articles,
        ]);
    }

    /**
     * Save Company Profile updates (FR-12).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:200'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'established_year' => ['required', 'integer', 'min:1800', 'max:2100'],
            'history' => ['required', 'string'],
            'vision' => ['required', 'string'],
            'mission_text' => ['required', 'string'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'whatsapp' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:150'],
            'operating_hours' => ['nullable', 'string', 'max:160'],
        ]);

        $mission = collect(preg_split('/\r\n|\r|\n/', $validated['mission_text']))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();

        $profile = CompanyProfile::firstOrNew(['id' => 1]);
        $profile->fill([
            'company_name' => $validated['company_name'],
            'tagline' => $validated['tagline'] ?? null,
            'established_year' => (int) $validated['established_year'],
            'history' => $validated['history'],
            'vision' => $validated['vision'],
            'mission' => $mission,
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'whatsapp' => $validated['whatsapp'],
            'email' => $validated['email'],
            'operating_hours' => $validated['operating_hours'] ?? null,
        ]);
        $profile->save();

        $this->recordAudit('company_profile.updated', 'CompanyProfile', $profile->id, null, [
            'company_name' => $profile->company_name,
        ], $request);

        return back()->with('status', 'Profil perusahaan PT Pabrik Gula Candi Baru berhasil diperbarui.');
    }

    /**
     * Add a gallery item to Company Profile (FR-12) supporting JPG/PNG file uploads.
     */
    public function storeGallery(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'caption' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:80'],
            'year_label' => ['nullable', 'string', 'max:40'],
            'image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:255'],
        ], [
            'image_file.mimes' => 'Format foto wajib berupa JPG, JPEG, atau PNG.',
            'image_file.max' => 'Ukuran file foto maksimal 5 MB.',
        ]);

        $imageUrl = ($validated['image_url'] ?? null) ?: null;

        if ($request->hasFile('image_file')) {
            $storedPath = $request->file('image_file')->store('gallery', 'public');
            if ($storedPath !== false) {
                $imageUrl = '/storage/' . ltrim($storedPath, '/');
            }
        }

        $galleryItem = GalleryItem::create([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'category' => $validated['category'],
            'year_label' => ($validated['year_label'] ?? null) ?: 'Arsip PG Candi Baru',
            'image_url' => $imageUrl,
            'sort_order' => ((int) GalleryItem::max('sort_order')) + 1,
            'is_active' => true,
        ]);

        $this->recordAudit('gallery.created', 'GalleryItem', $galleryItem->id, null, [
            'title' => $galleryItem->title,
            'image_url' => $galleryItem->image_url,
        ], $request);

        return back()->with('status', 'Item galeri heritage berhasil ditambahkan.');
    }

    /**
     * Delete a gallery item and its stored image file if uploaded locally (FR-12).
     */
    public function deleteGallery(GalleryItem $galleryItem): RedirectResponse
    {
        if ($galleryItem->image_url && str_starts_with($galleryItem->image_url, '/storage/gallery/')) {
            $relativePath = ltrim(substr($galleryItem->image_url, strlen('/storage/')), '/');
            Storage::disk('public')->delete($relativePath);
        }

        $galleryItem->delete();

        return back()->with('status', 'Item galeri berhasil dihapus.');
    }

    /**
     * List all articles in the Admin Dashboard.
     */
    public function articles(): View
    {
        $articles = Article::latest('published_at')->latest('id')->get();

        return view('admin.articles.index', [
            'articles' => $articles,
        ]);
    }

    /**
     * Render create or edit form for an Article.
     */
    public function editArticle(?Article $article = null): View
    {
        return view('admin.articles.form', [
            'article' => $article,
        ]);
    }

    /**
     * Store a new article or update an existing one, supporting JPG/PNG cover uploads.
     */
    public function saveArticle(Request $request, ?Article $article = null): RedirectResponse
    {
        $isUpdate = ($article !== null && $article->exists);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:80'],
            'author_name' => ['required', 'string', 'max:120'],
            'excerpt' => ['required', 'string', 'max:600'],
            'content' => ['required', 'string'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'is_published' => ['nullable', 'boolean'],
            'cover_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'cover_file.mimes' => 'Foto sampul artikel wajib berformat JPG, JPEG, atau PNG.',
            'cover_file.max' => 'Ukuran foto sampul maksimal 5 MB.',
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $slugQuery = Article::where('slug', $slug);
        if ($isUpdate) {
            $slugQuery->where('id', '!=', $article->id);
        }
        if ($slugQuery->exists()) {
            $slug .= '-' . strtolower(Str::random(4));
        }

        $coverUrl = $isUpdate ? $article->cover_image : null;
        if ($request->hasFile('cover_file')) {
            $storedPath = $request->file('cover_file')->store('articles', 'public');
            if ($storedPath !== false) {
                $coverUrl = '/storage/' . ltrim($storedPath, '/');
            }
        }

        $wordCount = str_word_count(strip_tags($validated['content']));
        $readingTime = ! empty($validated['reading_time_minutes'])
            ? (int) $validated['reading_time_minutes']
            : max(1, (int) ceil($wordCount / 180));

        $isPublished = $request->boolean('is_published', true);

        if ($isUpdate) {
            $oldValues = $article->only(['title', 'category', 'is_published']);
            $article->update([
                'title' => $validated['title'],
                'slug' => $slug,
                'category' => $validated['category'],
                'author_name' => $validated['author_name'],
                'excerpt' => $validated['excerpt'],
                'content' => $validated['content'],
                'cover_image' => $coverUrl,
                'reading_time_minutes' => $readingTime,
                'is_published' => $isPublished,
                'published_at' => $article->published_at ?? ($isPublished ? now() : null),
            ]);

            $this->recordAudit('article.updated', 'Article', $article->id, $oldValues, [
                'title' => $article->title,
                'category' => $article->category,
                'is_published' => $article->is_published,
            ], $request);

            return redirect()->route('admin.articles.edit', $article)
                ->with('status', 'Artikel "' . $article->title . '" berhasil diperbarui.');
        }

        $newArticle = Article::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'category' => $validated['category'],
            'author_name' => $validated['author_name'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'cover_image' => $coverUrl,
            'reading_time_minutes' => $readingTime,
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
        ]);

        $this->recordAudit('article.created', 'Article', $newArticle->id, null, [
            'title' => $newArticle->title,
            'slug' => $newArticle->slug,
        ], $request);

        return redirect()->route('admin.articles.index')
            ->with('status', 'Artikel baru "' . $newArticle->title . '" berhasil diterbitkan.');
    }

    /**
     * Delete an article and its uploaded cover file.
     */
    public function deleteArticle(Request $request, Article $article): RedirectResponse
    {
        if ($article->cover_image && str_starts_with($article->cover_image, '/storage/articles/')) {
            $relativePath = ltrim(substr($article->cover_image, strlen('/storage/')), '/');
            Storage::disk('public')->delete($relativePath);
        }

        $title = $article->title;
        $id = $article->id;
        $article->delete();

        $this->recordAudit('article.deleted', 'Article', $id, ['title' => $title], null, $request);

        return redirect()->route('admin.articles.index')
            ->with('status', 'Artikel "' . $title . '" berhasil dihapus.');
    }

    /**
     * Mark all admin notifications as read (FR-16).
     */
    public function markNotificationsRead(): RedirectResponse
    {
        AdminNotification::where('is_read', false)->update(['is_read' => true]);

        return back()->with('status', 'Semua notifikasi ditandai telah dibaca.');
    }

    /**
     * Record an entry in the audit log (NFR Audit).
     *
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    protected function recordAudit(
        string $action,
        string $entityType,
        int $entityId,
        ?array $oldValues,
        ?array $newValues,
        Request $request,
    ): void {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }
}

