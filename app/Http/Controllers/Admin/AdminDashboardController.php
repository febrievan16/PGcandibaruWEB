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
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->select(['status', 'ticket_qty', 'total_amount', 'paid_at', 'created_at'])
            ->get();

        $paidOrders = $orders->where('status', 'paid');
        $totalRevenue = (int) $paidOrders->sum('total_amount');
        $paidOrdersCount = $paidOrders->count();
        $pendingOrdersCount = $orders->where('status', 'pending')->count();
        $ticketsSoldCount = (int) $paidOrders->sum('ticket_qty');

        // Build 8-month grouped bar chart statistics from in-memory orders (zero extra SQL queries)
        $monthShortNames = [
            1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR',
            5 => 'MEI', 6 => 'JUN', 7 => 'JUL', 8 => 'AGU',
            9 => 'SEP', 10 => 'OKT', 11 => 'NOV', 12 => 'DES',
        ];

        $ordersByYearMonth = $orders->groupBy(function (Order $order): string {
            $refDate = $order->paid_at ?? $order->created_at ?? now();

            return $refDate->format('Y-m');
        });

        $rawMonths = [];
        $maxChartVal = 1;
        for ($i = 7; $i >= 0; $i--) {
            $monthDate = now()->startOfMonth()->subMonths($i);
            $ymKey = $monthDate->format('Y-m');
            $monthGroup = $ordersByYearMonth->get($ymKey, collect());

            $mTickets = (int) $monthGroup->where('status', 'paid')->sum('ticket_qty');
            $mPaid = $monthGroup->where('status', 'paid')->count();
            $mPending = $monthGroup->where('status', 'pending')->count();
            $mRevenue = (int) $monthGroup->where('status', 'paid')->sum('total_amount');

            $maxChartVal = max($maxChartVal, $mTickets, $mPaid, $mPending);

            $rawMonths[] = [
                'ym' => $ymKey,
                'label' => $monthShortNames[(int) $monthDate->format('n')] ?? strtoupper($monthDate->format('M')),
                'full_label' => $monthDate->translatedFormat('F Y'),
                'tickets_sold' => $mTickets,
                'paid_orders' => $mPaid,
                'pending_orders' => $mPending,
                'revenue' => $mRevenue,
            ];
        }

        $monthlyChartData = collect($rawMonths)->map(function (array $item) use ($maxChartVal): array {
            $item['tickets_pct'] = $item['tickets_sold'] > 0
                ? max(14, (int) round(($item['tickets_sold'] / $maxChartVal) * 100))
                : 6;
            $item['paid_pct'] = $item['paid_orders'] > 0
                ? max(14, (int) round(($item['paid_orders'] / $maxChartVal) * 100))
                : 6;
            $item['pending_pct'] = $item['pending_orders'] > 0
                ? max(14, (int) round(($item['pending_orders'] / $maxChartVal) * 100))
                : 6;

            return $item;
        });

        $schedules = Schedule::with('event:id,title')
            ->orderBy('tour_date')
            ->orderBy('start_time')
            ->get();

        $totalQuotaCapacity = (int) $schedules->sum('quota_total');
        $totalQuotaBooked = (int) $schedules->sum('quota_booked');
        $overallOccupancyPct = $totalQuotaCapacity > 0
            ? min(100, (int) round(($totalQuotaBooked / $totalQuotaCapacity) * 100))
            : 0;

        // Build Event Tour Donut Chart distribution from loaded schedules (zero extra SQL queries)
        $donutPalette = ['#fe7096', '#36a2eb', '#4bc0c0', '#a461d8', '#ff9f40'];
        $circumference = 84.823; // 2 * pi * 13.5
        $groupedByEvent = $schedules->groupBy('event_id');
        $totalEventWeight = $totalQuotaBooked > 0 ? $totalQuotaBooked : max(1, $totalQuotaCapacity);
        $runningOffset = 0.0;

        $eventDistribution = $groupedByEvent->values()->map(function ($eventSchedules, int $idx) use ($donutPalette, $totalQuotaBooked, $totalEventWeight, $circumference, &$runningOffset): array {
            $firstSchedule = $eventSchedules->first();
            $title = $firstSchedule?->event?->title ?? 'Program Tour Heritage';
            $booked = (int) $eventSchedules->sum('quota_booked');
            $capacity = (int) $eventSchedules->sum('quota_total');
            $weight = $totalQuotaBooked > 0 ? $booked : $capacity;
            $pct = $totalEventWeight > 0 ? (int) round(($weight / $totalEventWeight) * 100) : 0;

            $arcLen = round(($pct / 100) * $circumference, 2);
            $currentOffset = round(-$runningOffset, 2);
            $runningOffset += $arcLen;

            return [
                'title' => $title,
                'booked' => $booked,
                'capacity' => $capacity,
                'percentage' => $pct,
                'color' => $donutPalette[$idx % count($donutPalette)],
                'dash_array' => "{$arcLen} {$circumference}",
                'dash_offset' => (string) $currentOffset,
            ];
        });

        $occupancyEvents = $schedules
            ->pluck('event')
            ->filter()
            ->unique('id')
            ->values();

        $filterEventId = (string) $request->query('event_id', 'all');
        $filterSessionStatus = (string) $request->query('session_status', 'all');

        $notifications = AdminNotification::query()
            ->latest('created_at')
            ->limit(8)
            ->get();

        $unreadNotificationsCount = AdminNotification::where('is_read', false)->count();

        $auditLogs = AuditLog::with('user:id,name')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'totalRevenue' => $totalRevenue,
            'paidOrdersCount' => $paidOrdersCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'ticketsSoldCount' => $ticketsSoldCount,
            'totalQuotaCapacity' => $totalQuotaCapacity,
            'totalQuotaBooked' => $totalQuotaBooked,
            'overallOccupancyPct' => $overallOccupancyPct,
            'monthlyChartData' => $monthlyChartData,
            'eventDistribution' => $eventDistribution,
            'schedules' => $schedules,
            'occupancyEvents' => $occupancyEvents,
            'filterEventId' => $filterEventId,
            'filterSessionStatus' => $filterSessionStatus,
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
        $query = Order::with(['schedule:id,event_id,tour_date,session_code,start_time,end_time'])
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
            $inspectCode = (string) $request->query('inspect');
            $selectedOrder = $orders->getCollection()->firstWhere('order_code', $inspectCode)
                ?? Order::where('order_code', $inspectCode)->first();
        }
        if (! $selectedOrder && $orders->count() > 0) {
            $selectedOrder = $orders->first();
        }

        $selectedOrder?->loadMissing(['schedule.event', 'tickets']);

        $schedules = Schedule::with('event:id,title')
            ->select(['id', 'event_id', 'tour_date', 'session_code'])
            ->orderByDesc('tour_date')
            ->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'selectedOrder' => $selectedOrder,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Render detailed Monthly Transaction Report, Per-Buyer Transaction Descriptions, and Most Popular Events Analytics.
     */
    public function transactions(Request $request): View
    {
        $allOrders = Order::with([
            'schedule:id,event_id,tour_date,session_code,start_time,end_time',
            'schedule.event:id,title,slug,ticket_price',
        ])
            ->latest('created_at')
            ->get();

        $events = Event::query()
            ->select(['id', 'title', 'slug', 'ticket_price', 'status'])
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        // Filter parameters for the detailed transactions table
        $selectedMonth = (string) $request->query('month', '');
        $selectedEventId = $request->integer('event_id') ?: null;
        $selectedStatus = (string) $request->query('status', '');
        $search = trim((string) $request->query('search', ''));

        $filteredOrders = $allOrders->filter(function (Order $order) use ($selectedMonth, $selectedEventId, $selectedStatus, $search): bool {
            if ($selectedMonth !== '') {
                $orderMonth = ($order->paid_at ?? $order->created_at)?->format('Y-m');
                if ($orderMonth !== $selectedMonth) {
                    return false;
                }
            }

            if ($selectedEventId !== null && $order->schedule?->event_id !== $selectedEventId) {
                return false;
            }

            if ($selectedStatus !== '' && $order->status !== $selectedStatus) {
                return false;
            }

            if ($search !== '') {
                $haystack = strtolower($order->order_code . ' ' . $order->buyer_name . ' ' . $order->buyer_email);
                if (! str_contains($haystack, strtolower($search))) {
                    return false;
                }
            }

            return true;
        })->values();

        // 1. Build 6-Month Report Series (chronological & reverse-chronological)
        $monthKeys = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthKeys->push(now()->subMonths($i)->format('Y-m'));
        }
        foreach ($allOrders as $order) {
            $mKey = ($order->paid_at ?? $order->created_at)?->format('Y-m');
            if ($mKey && ! $monthKeys->contains($mKey)) {
                $monthKeys->push($mKey);
            }
        }
        $monthKeys = $monthKeys->sort()->values();

        $monthlyReport = $monthKeys->map(function (string $monthKey) use ($allOrders): array {
            $monthDate = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $monthKey . '-01');
            $monthOrders = $allOrders->filter(fn (Order $o): bool => (($o->paid_at ?? $o->created_at)?->format('Y-m')) === $monthKey);
            $paidMonthOrders = $monthOrders->where('status', 'paid');
            $pendingMonthOrders = $monthOrders->where('status', 'pending');
            $voidMonthOrders = $monthOrders->whereIn('status', ['expired', 'cancelled']);

            $topEventGroup = $monthOrders
                ->groupBy(fn (Order $o): string => $o->schedule?->event?->title ?? 'Lainnya')
                ->sortByDesc(fn ($group): int => (int) $group->sum('ticket_qty'));

            $topEventTitle = $topEventGroup->keys()->first() ?? '-';
            $topEventTickets = $topEventGroup->first() ? (int) $topEventGroup->first()->sum('ticket_qty') : 0;

            $totalCount = $monthOrders->count();
            $paidCount = $paidMonthOrders->count();

            return [
                'month_key' => $monthKey,
                'month_label' => $monthDate->translatedFormat('F Y'),
                'short_label' => $monthDate->translatedFormat('M Y'),
                'total_orders' => $totalCount,
                'paid_orders' => $paidCount,
                'pending_orders' => $pendingMonthOrders->count(),
                'void_orders' => $voidMonthOrders->count(),
                'tickets_sold' => (int) $paidMonthOrders->sum('ticket_qty'),
                'revenue' => (int) $paidMonthOrders->sum('total_amount'),
                'pending_amount' => (int) $pendingMonthOrders->sum('total_amount'),
                'conversion_rate' => $totalCount > 0 ? (int) round(($paidCount / $totalCount) * 100) : 0,
                'top_event_title' => $topEventTitle,
                'top_event_tickets' => $topEventTickets,
            ];
        })->values();

        $maxMonthlyRevenue = max(1, (int) $monthlyReport->max('revenue'));
        $maxMonthlyTickets = max(1, (int) $monthlyReport->max('tickets_sold'));

        // 2. Most Frequently Ordered Events Ranking (based on filtered or all orders)
        $totalOrdersCount = max(1, $allOrders->count());
        $totalPaidTickets = max(1, (int) $allOrders->where('status', 'paid')->sum('ticket_qty'));
        $totalPaidRevenue = max(1, (int) $allOrders->where('status', 'paid')->sum('total_amount'));

        $eventRankings = $events->map(function (Event $event) use ($allOrders, $totalOrdersCount, $totalPaidTickets, $totalPaidRevenue): array {
            $eventOrders = $allOrders->filter(fn (Order $o): bool => $o->schedule?->event_id === $event->id);
            $paidEventOrders = $eventOrders->where('status', 'paid');

            $ordersCount = $eventOrders->count();
            $paidCount = $paidEventOrders->count();
            $ticketsSold = (int) $paidEventOrders->sum('ticket_qty');
            $totalTicketsRequested = (int) $eventOrders->sum('ticket_qty');
            $revenue = (int) $paidEventOrders->sum('total_amount');
            $uniqueBuyers = $eventOrders->pluck('buyer_email')->map(fn ($e): string => strtolower((string) $e))->unique()->count();

            return [
                'event_id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'ticket_price' => $event->ticket_price,
                'total_orders' => $ordersCount,
                'paid_orders' => $paidCount,
                'tickets_sold' => $ticketsSold,
                'total_tickets_requested' => $totalTicketsRequested,
                'revenue' => $revenue,
                'unique_buyers' => $uniqueBuyers,
                'avg_tickets_per_order' => $ordersCount > 0 ? round($totalTicketsRequested / $ordersCount, 1) : 0.0,
                'order_share_pct' => (int) round(($ordersCount / $totalOrdersCount) * 100),
                'ticket_share_pct' => (int) round(($ticketsSold / $totalPaidTickets) * 100),
                'revenue_share_pct' => (int) round(($revenue / $totalPaidRevenue) * 100),
            ];
        })->sortByDesc(fn (array $item): array => [$item['tickets_sold'], $item['total_orders'], $item['revenue']])->values();

        // 3. Per-Buyer Transaction Summary (Accumulated Activity per Customer)
        $buyerSummaries = $allOrders
            ->groupBy(fn (Order $o): string => strtolower(trim($o->buyer_email)))
            ->map(function ($buyerOrders): array {
                /** @var Order $latest */
                $latest = $buyerOrders->first();
                $paidOrders = $buyerOrders->where('status', 'paid');
                $totalOrders = $buyerOrders->count();
                $paidCount = $paidOrders->count();
                $ticketsBought = (int) $paidOrders->sum('ticket_qty');
                $totalTicketsAll = (int) $buyerOrders->sum('ticket_qty');
                $totalSpent = (int) $paidOrders->sum('total_amount');

                $eventNames = $buyerOrders
                    ->map(fn (Order $o): string => $o->schedule?->event?->title ?? 'Event Tour')
                    ->unique()
                    ->values()
                    ->all();

                $channels = $buyerOrders
                    ->map(fn (Order $o): string => $o->paymentChannelLabel())
                    ->unique()
                    ->values()
                    ->all();

                $isRegistered = $buyerOrders->contains(fn (Order $o): bool => $o->user_id !== null);

                return [
                    'buyer_name' => $latest->buyer_name,
                    'buyer_email' => $latest->buyer_email,
                    'buyer_phone' => $latest->buyer_phone,
                    'is_registered' => $isRegistered,
                    'total_orders' => $totalOrders,
                    'paid_orders' => $paidCount,
                    'tickets_bought' => $ticketsBought,
                    'total_tickets_all' => $totalTicketsAll,
                    'total_spent' => $totalSpent,
                    'events_ordered' => $eventNames,
                    'channels_used' => $channels,
                    'last_order_at' => $latest->created_at,
                    'description' => sprintf(
                        '%s telah melakukan %d kali transaksi (%d lunas) dengan total %d tiket senilai Rp %s pada program: %s.',
                        $latest->buyer_name,
                        $totalOrders,
                        $paidCount,
                        $totalTicketsAll,
                        number_format($totalSpent, 0, ',', '.'),
                        implode(', ', $eventNames)
                    ),
                ];
            })
            ->sortByDesc(fn (array $b): array => [$b['total_spent'], $b['tickets_bought'], $b['total_orders']])
            ->values();

        // 4. Payment Channel Distribution
        $paidOrdersAll = $allOrders->where('status', 'paid');
        $paymentChannelStats = $paidOrdersAll
            ->groupBy(fn (Order $o): string => $o->paymentChannelLabel())
            ->map(function ($group, string $channelLabel) use ($totalPaidRevenue): array {
                $rev = (int) $group->sum('total_amount');

                return [
                    'channel' => $channelLabel,
                    'orders_count' => $group->count(),
                    'tickets_count' => (int) $group->sum('ticket_qty'),
                    'revenue' => $rev,
                    'share_pct' => (int) round(($rev / $totalPaidRevenue) * 100),
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        return view('admin.transactions.index', [
            'allOrders' => $allOrders,
            'filteredOrders' => $filteredOrders,
            'events' => $events,
            'monthlyReport' => $monthlyReport,
            'maxMonthlyRevenue' => $maxMonthlyRevenue,
            'maxMonthlyTickets' => $maxMonthlyTickets,
            'eventRankings' => $eventRankings,
            'buyerSummaries' => $buyerSummaries,
            'paymentChannelStats' => $paymentChannelStats,
            'selectedMonth' => $selectedMonth,
            'selectedEventId' => $selectedEventId,
            'selectedStatus' => $selectedStatus,
            'search' => $search,
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
                $order = $this->midtransService->handleNotification([
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

        return back()->with('status', "Status pesanan {$order->order_code} berhasil diperbarui menjadi {$order->statusLabel()}.");
    }

    /**
     * List all events & schedules for Admin management (FR-13).
     */
    public function events(): View
    {
        $events = Event::withCount('schedules')
            ->withExists(['schedules as has_valid_schedules' => function ($query): void {
                $query->where('quota_total', '>', 0);
            }])
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
        $recentCheckIns = \App\Models\Ticket::with([
            'order:id,schedule_id,order_code,buyer_name',
            'order.schedule:id,event_id,tour_date,session_code',
            'order.schedule.event:id,title',
        ])
            ->where('status', 'used')
            ->latest('checked_in_at')
            ->limit(10)
            ->get();

        $sampleValidTickets = \App\Models\Ticket::with('order:id,buyer_name')
            ->select(['id', 'order_id', 'ticket_code', 'qr_payload', 'status'])
            ->where('status', 'valid')
            ->limit(4)
            ->get();

        return view('admin.tickets.scanner', [
            'recentCheckIns' => $recentCheckIns,
            'sampleValidTickets' => $sampleValidTickets,
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
        $profile = CompanyProfile::current();
        $galleryItems = GalleryItem::orderBy('sort_order')->get();

        return view('admin.profile.edit', [
            'profile' => $profile,
            'galleryItems' => $galleryItems,
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
     * Add a gallery item to Company Profile (FR-12) supporting single or multiple JPG/PNG file uploads.
     */
    public function storeGallery(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'caption' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:80'],
            'year_label' => ['nullable', 'string', 'max:40'],
            'image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'image_files' => ['nullable', 'array'],
            'image_files.*' => ['file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:255'],
        ], [
            'image_file.mimes' => 'Format foto wajib berupa JPG, JPEG, atau PNG.',
            'image_file.max' => 'Ukuran file foto maksimal 5 MB.',
            'image_files.*.mimes' => 'Setiap foto wajib berformat JPG, JPEG, atau PNG.',
            'image_files.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ]);

        $photos = [];

        if ($request->hasFile('image_file')) {
            $storedPath = $request->file('image_file')->store('gallery', 'public');
            if ($storedPath !== false) {
                $photos[] = '/storage/' . ltrim($storedPath, '/');
            }
        }

        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                $storedPath = $file->store('gallery', 'public');
                if ($storedPath !== false) {
                    $photos[] = '/storage/' . ltrim($storedPath, '/');
                }
            }
        }

        if (! empty($validated['image_url'])) {
            $photos[] = $validated['image_url'];
        }

        $primaryImageUrl = $photos[0] ?? null;

        $galleryItem = GalleryItem::create([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'category' => $validated['category'],
            'year_label' => ($validated['year_label'] ?? null) ?: 'Arsip PG Candi Baru',
            'image_url' => $primaryImageUrl,
            'photos' => $photos,
            'sort_order' => ((int) GalleryItem::max('sort_order')) + 1,
            'is_active' => true,
        ]);

        $this->recordAudit('gallery.created', 'GalleryItem', $galleryItem->id, null, [
            'title' => $galleryItem->title,
            'photos_count' => count($photos),
        ], $request);

        return back()->with('status', 'Koleksi galeri heritage berhasil ditambahkan (' . max(1, count($photos)) . ' foto).');
    }

    /**
     * Append additional JPG/PNG photos to an existing gallery item.
     */
    public function addGalleryPhotos(Request $request, GalleryItem $galleryItem): RedirectResponse
    {
        $request->validate([
            'image_files' => ['required', 'array', 'min:1'],
            'image_files.*' => ['file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'image_files.required' => 'Pilih minimal 1 file foto JPG/PNG.',
            'image_files.*.mimes' => 'Setiap foto wajib berformat JPG, JPEG, atau PNG.',
            'image_files.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ]);

        $existingPhotos = is_array($galleryItem->photos) ? $galleryItem->photos : [];
        if ($galleryItem->image_url && ! in_array($galleryItem->image_url, $existingPhotos, true)) {
            array_unshift($existingPhotos, $galleryItem->image_url);
        }

        foreach ($request->file('image_files') as $file) {
            $storedPath = $file->store('gallery', 'public');
            if ($storedPath !== false) {
                $existingPhotos[] = '/storage/' . ltrim($storedPath, '/');
            }
        }

        $galleryItem->update([
            'image_url' => $existingPhotos[0] ?? $galleryItem->image_url,
            'photos' => array_values(array_unique($existingPhotos)),
        ]);

        return back()->with('status', 'Foto tambahan berhasil dimasukkan ke galeri "' . $galleryItem->title . '".');
    }

    /**
     * Delete a gallery item and all its stored image files if uploaded locally (FR-12).
     */
    public function deleteGallery(GalleryItem $galleryItem): RedirectResponse
    {
        $allUrls = is_array($galleryItem->photos) ? $galleryItem->photos : [];
        if ($galleryItem->image_url) {
            $allUrls[] = $galleryItem->image_url;
        }

        foreach (array_unique($allUrls) as $url) {
            if (is_string($url) && str_starts_with($url, '/storage/gallery/')) {
                $relativePath = ltrim(substr($url, strlen('/storage/')), '/');
                Storage::disk('public')->delete($relativePath);
            }
        }

        $galleryItem->delete();

        return back()->with('status', 'Item galeri berhasil dihapus.');
    }

    /**
     * List all articles in the Admin Dashboard.
     */
    public function articles(): View
    {
        $articles = Article::query()
            ->select([
                'id',
                'slug',
                'title',
                'category',
                'author_name',
                'cover_image',
                'reading_time_minutes',
                'is_published',
                'published_at',
                'created_at',
            ])
            ->latest('published_at')
            ->latest('id')
            ->get();

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

