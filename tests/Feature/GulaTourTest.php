<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\Ticket;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GulaTourTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
    }

    /**
     * M1 (FR-01, FEATURES.md §2): Company profile renders history, vision, mission, and heritage placeholder when gallery is empty.
     */
    public function test_company_profile_and_empty_gallery_placeholder(): void
    {
        $response = $this->get('/profil');
        $response->assertStatus(200);
        $response->assertSee('PT Pabrik Gula Candi Baru');
        $response->assertSee('1911');

        GalleryItem::query()->delete();

        $emptyGalleryResponse = $this->get('/profil');
        $emptyGalleryResponse->assertStatus(200);
        $emptyGalleryResponse->assertSee('Koleksi Foto Arsip Sedang Dalam Kurasi');
    }

    /**
     * M2 (FR-02, FR-09, DEC-001, DEC-003): Active events catalog hides draft events, and schedules API returns real-time remaining quota.
     */
    public function test_catalog_shows_only_active_events_and_schedules_api_returns_quota(): void
    {
        $catalog = $this->get('/events');
        $catalog->assertStatus(200);
        $catalog->assertSee('Tour Heritage & Edukasi Pabrik Gula Candi Baru');
        $catalog->assertDontSee('Tur Khusus Pembukaan Musim Giling (Konfigurasi TBD)');

        $api = $this->getJson('/api/v1/events/tour-pabrik-candi-baru/schedules');
        $api->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'tour-pabrik-candi-baru')
            ->assertJsonPath('data.ticket_price', 75000)
            ->assertJsonPath('data.currency', 'IDR');
    }

    /**
     * M3 (DEC-003): Step 2 buyer form is NOT rendered before a valid schedule is selected, and IS rendered after selection.
     */
    public function test_booking_wizard_enforces_schedule_selection_first(): void
    {
        $event = Event::where('slug', 'tour-pabrik-candi-baru')->firstOrFail();
        $openSchedule = $event->schedules->first(fn (Schedule $s): bool => $s->isAvailable());

        $step1View = $this->get("/events/{$event->slug}");
        $step1View->assertStatus(200);
        $step1View->assertSee('Langkah 2 Terkunci (Aturan DEC-003)');
        $step1View->assertDontSee('id="booking-wizard-form"', false);

        $step2View = $this->get("/events/{$event->slug}?schedule_id={$openSchedule->id}");
        $step2View->assertStatus(200);
        $step2View->assertSee('id="booking-wizard-form"', false);
    }

    /**
     * M3 (DEC-001, FR-09, TASK-06): API order creation calculates flat price server-side, locks quota atomically, and enforces idempotency.
     */
    public function test_create_order_calculates_flat_price_decrements_quota_and_respects_idempotency(): void
    {
        $event = Event::where('slug', 'tour-pabrik-candi-baru')->firstOrFail();
        $schedule = $event->schedules()->where('status', 'open')->where('quota_booked', '<', 10)->firstOrFail();
        $initialRemaining = $schedule->remainingQuota();

        $headers = ['X-Idempotency-Key' => 'idem-key-test-12345'];
        $payload = [
            'schedule_id' => $schedule->id,
            'quantity' => 3,
            'customer' => [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'phone' => '081234567890',
            ],
            'guest_checkout' => true,
        ];

        $res1 = $this->postJson('/api/v1/orders', $payload, $headers);
        $res1->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.quantity', 3)
            ->assertJsonPath('data.total_amount', 75000 * 3);

        $orderCode = $res1->json('data.order_code');

        // Duplicate submission with same X-Idempotency-Key must return the exact same order without double-decrementing quota
        $res2 = $this->postJson('/api/v1/orders', $payload, $headers);
        $res2->assertStatus(201)
            ->assertJsonPath('data.order_code', $orderCode);

        $this->assertEquals($initialRemaining - 3, $schedule->fresh()->remainingQuota());

        // Exceeding remaining quota must return 409 QUOTA_EXCEEDED
        $resExceed = $this->postJson('/api/v1/orders', [
            'schedule_id' => $schedule->id,
            'quantity' => 999,
            'customer' => [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'phone' => '081234567890',
            ],
        ]);
        $resExceed->assertStatus(409)
            ->assertJsonPath('error.code', 'QUOTA_EXCEEDED');
    }

    /**
     * M4 (DEC-002, FR-06, FR-07, TASK-08, TASK-09): Midtrans webhook verifies signature, updates status idempotently, and issues QR tickets.
     */
    public function test_midtrans_webhook_verifies_signature_and_issues_tickets_idempotently(): void
    {
        $order = Order::where('status', 'pending')->firstOrFail();
        $midtransService = app(MidtransService::class);

        $grossAmount = $order->total_amount . '.00';
        $validSignature = $midtransService->computeSignature($order->order_code, '200', $grossAmount);

        // 1. Invalid signature must be rejected with 403
        $tampered = $this->postJson('/api/v1/payments/notification', [
            'order_id' => $order->order_code,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'gross_amount' => $grossAmount,
            'status_code' => '200',
            'signature_key' => 'invalid-signature-key',
        ]);
        $tampered->assertStatus(403);

        // 2. Valid settlement webhook updates order to paid and creates ticket_qty tickets
        $validRes = $this->postJson('/api/v1/payments/notification', [
            'order_id' => $order->order_code,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'gross_amount' => $grossAmount,
            'status_code' => '200',
            'signature_key' => $validSignature,
        ]);
        $validRes->assertStatus(200)->assertJsonPath('success', true);

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertCount($order->ticket_qty, $order->tickets);

        // 3. Duplicate webhook call must not duplicate tickets
        $replayRes = $this->postJson('/api/v1/payments/notification', [
            'order_id' => $order->order_code,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'gross_amount' => $grossAmount,
            'status_code' => '200',
            'signature_key' => $validSignature,
        ]);
        $replayRes->assertStatus(200);
        $this->assertCount($order->ticket_qty, $order->fresh()->tickets);
    }

    /**
     * M5 (DEC-004, FR-10, TASK-10): Guest signed token access vs. Registered User ownership isolation.
     */
    public function test_order_access_isolation_for_guest_and_registered_user(): void
    {
        $paidOrder = Order::where('status', 'paid')->whereNotNull('user_id')->firstOrFail();

        // Guest without token -> 403
        $this->getJson("/api/v1/orders/{$paidOrder->order_code}")
            ->assertStatus(403);

        // Guest with valid signed token -> 200
        $this->getJson("/api/v1/orders/{$paidOrder->order_code}?token={$paidOrder->access_token}")
            ->assertStatus(200)
            ->assertJsonPath('data.order_code', $paidOrder->order_code);

        // Owner with Bearer token -> 200
        $this->getJson("/api/v1/orders/{$paidOrder->order_code}", [
            'Authorization' => 'Bearer gulatour-user-token-demo',
        ])->assertStatus(200);
    }

    /**
     * M6 (FR-04, FR-13..15, TASK-12, TASK-13): Admin role guard, FR-04 event activation guard, quota guard, CSV export, and QR check-in.
     */
    public function test_admin_guards_fr04_validation_csv_export_and_qr_checkin(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $regularUser = User::where('role', 'user')->firstOrFail();

        // Regular user cannot access admin API
        $this->getJson('/api/v1/admin/orders', [
            'Authorization' => 'Bearer ' . $regularUser->api_token,
        ])->assertStatus(403);

        // Admin can export CSV (FR-15)
        $csvRes = $this->get('/api/v1/admin/orders?export=csv', [
            'Authorization' => 'Bearer ' . $admin->api_token,
        ]);
        $csvRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $csvRes->headers->get('Content-Type'));

        // Admin FR-04 guard: cannot activate draft event when ticket_price = 0 or no schedules exist
        $draftEvent = Event::where('slug', 'tur-khusus-musim-giling-tbd')->firstOrFail();
        $activateAttempt = $this->actingAs($admin)->put(route('admin.events.update', $draftEvent), [
            'title' => $draftEvent->title,
            'description' => $draftEvent->description,
            'duration_minutes' => 120,
            'meeting_point' => 'Gerbang Utama',
            'ticket_price' => 0,
            'status' => 'active',
        ]);
        $activateAttempt->assertSessionHasErrors('status');
        $this->assertEquals('draft', $draftEvent->fresh()->status);

        // Quota guard: cannot set quota_total below quota_booked (FEATURES.md §7)
        $bookedSchedule = Schedule::where('quota_booked', '>', 5)->firstOrFail();
        $quotaAttempt = $this->actingAs($admin)->put(route('admin.schedules.update', $bookedSchedule), [
            'quota_total' => 2,
            'status' => 'open',
        ]);
        $quotaAttempt->assertSessionHasErrors('schedule');

        // QR Ticket Check-In Validation (FR-14): First scan succeeds, second scan rejected as used
        $validTicket = Ticket::where('status', 'valid')->firstOrFail();

        $scan1 = $this->postJson('/api/v1/admin/tickets/validate', [
            'qr_payload' => $validTicket->qr_payload,
        ], [
            'Authorization' => 'Bearer ' . $admin->api_token,
        ]);
        $scan1->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'used');

        $scan2 = $this->postJson('/api/v1/admin/tickets/validate', [
            'qr_payload' => $validTicket->qr_payload,
        ], [
            'Authorization' => 'Bearer ' . $admin->api_token,
        ]);
        $scan2->assertStatus(409)
            ->assertJsonPath('error.code', 'TICKET_ALREADY_USED');

        // Web scanner flow (following redirect to /admin/tickets/scanner with flashed scan_result)
        $webScan = $this->actingAs($admin)
            ->from(route('admin.tickets.scanner'))
            ->followingRedirects()
            ->post(route('admin.tickets.validate'), [
                'code' => $validTicket->ticket_code,
            ]);
        $webScan->assertStatus(200)
            ->assertSee($validTicket->ticket_code)
            ->assertSee('TICKET_ALREADY_USED');

        // Admin gallery creation with multiple JPG/PNG file uploads & appending additional photos
        Storage::fake('public');
        $fakePng1 = UploadedFile::fake()->create('lokomotif-1.png', 120, 'image/png');
        $fakeJpg2 = UploadedFile::fake()->create('lokomotif-2.jpg', 120, 'image/jpeg');
        $galleryPost = $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->post(route('admin.profile.gallery.store'), [
                'title' => 'Lokomotif Uap Candi Baru',
                'category' => 'Arsip Sejarah',
                'image_files' => [$fakePng1, $fakeJpg2],
            ]);
        $galleryPost->assertRedirect(route('admin.profile.edit'));
        $createdGallery = GalleryItem::where('title', 'Lokomotif Uap Candi Baru')->firstOrFail();
        $this->assertNotNull($createdGallery->image_url);
        $this->assertCount(2, $createdGallery->gallerySlides());

        $fakePng3 = UploadedFile::fake()->create('lokomotif-3.png', 120, 'image/png');
        $appendPost = $this->actingAs($admin)
            ->post(route('admin.profile.gallery.photos.store', $createdGallery), [
                'image_files' => [$fakePng3],
            ]);
        $appendPost->assertRedirect();
        $this->assertCount(3, $createdGallery->fresh()->gallerySlides());

        // Public & Admin Article flow on /profil and /admin/articles
        $article = Article::firstOrFail();
        $this->get('/profil')
            ->assertStatus(200)
            ->assertSee($article->title);

        $this->actingAs($admin)
            ->get('/profil')
            ->assertStatus(200)
            ->assertSee('Edit Artikel Ini');

        $this->get(route('articles.show', $article->slug))
            ->assertStatus(200)
            ->assertSee($article->title);

        $updateArticleRes = $this->actingAs($admin)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title . ' (Diperbarui)',
                'category' => 'Arsip & Sejarah',
                'author_name' => 'Tim Kurator Sejarah PG Candi Baru',
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'reading_time_minutes' => 5,
                'is_published' => 1,
                'cover_file' => UploadedFile::fake()->create('sampul-artikel.jpg', 150, 'image/jpeg'),
            ]);
        $updateArticleRes->assertRedirect(route('admin.articles.edit', $article));
        $this->assertStringContainsString('(Diperbarui)', $article->fresh()->title);
        $this->assertStringStartsWith('/storage/articles/', (string) $article->fresh()->cover_image);

        $this->assertGreaterThan(0, AuditLog::count());
    }

    /**
     * Performance & N+1 Audit: Verify every public, user, and admin page renders with zero lazy-loading (N+1) and minimal SQL queries.
     */
    public function test_all_pages_render_without_n_plus_one_or_lazy_loading_and_within_query_budget(): void
    {
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(true);

        // Verify CompanyProfile::current() works with database cache driver (serializable_classes = false)
        config(['cache.default' => 'database']);
        \Illuminate\Support\Facades\Cache::forget(\App\Models\CompanyProfile::CACHE_KEY);
        $firstCall = \App\Models\CompanyProfile::current();
        $secondCall = \App\Models\CompanyProfile::current();
        $this->assertInstanceOf(\App\Models\CompanyProfile::class, $firstCall);
        $this->assertInstanceOf(\App\Models\CompanyProfile::class, $secondCall);
        $this->assertEquals($firstCall->company_name, $secondCall->company_name);
        config(['cache.default' => 'array']);

        $admin = User::where('role', 'admin')->firstOrFail();
        $regularUser = User::where('role', 'user')->firstOrFail();
        $event = Event::where('slug', 'tour-pabrik-candi-baru')->firstOrFail();
        $article = Article::firstOrFail();
        $paidOrder = Order::where('status', 'paid')->firstOrFail();

        // Warm company profile cache once, then verify public pages
        $publicRoutes = [
            '/',
            '/profil',
            '/profil/artikel',
            '/profil/artikel/' . $article->slug,
            '/events',
            '/events/' . $event->slug,
            '/orders/lookup',
            "/orders/{$paidOrder->order_code}?token={$paidOrder->access_token}",
            "/orders/{$paidOrder->order_code}/e-ticket?token={$paidOrder->access_token}",
        ];

        foreach ($publicRoutes as $uri) {
            \Illuminate\Support\Facades\DB::flushQueryLog();
            \Illuminate\Support\Facades\DB::enableQueryLog();

            $this->get($uri)->assertStatus(200);

            $queryCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
            $this->assertLessThanOrEqual(6, $queryCount, "Public URI [{$uri}] executed {$queryCount} queries (expected <= 6).");
        }

        // Verify Registered User Order History page (/akun/pesanan)
        \Illuminate\Support\Facades\DB::flushQueryLog();
        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->actingAs($regularUser)->get('/akun/pesanan')->assertStatus(200);
        $this->assertLessThanOrEqual(4, count(\Illuminate\Support\Facades\DB::getQueryLog()));

        // Verify Admin routes (especially /admin/events where canBeActivated() runs in a loop, and /admin/orders master-detail)
        $adminRoutes = [
            '/admin' => 7,
            '/admin/transactions' => 4,
            '/admin/orders' => 7,
            '/admin/events' => 2,
            '/admin/events/' . $event->id . '/edit' => 3,
            '/admin/tickets/scanner' => 6,
            '/admin/profile' => 3,
            '/admin/articles' => 2,
            '/admin/articles/' . $article->id . '/edit' => 2,
        ];

        foreach ($adminRoutes as $uri => $maxQueries) {
            \Illuminate\Support\Facades\DB::flushQueryLog();
            \Illuminate\Support\Facades\DB::enableQueryLog();

            $this->actingAs($admin)->get($uri)->assertStatus(200);

            $queryCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
            $this->assertLessThanOrEqual($maxQueries, $queryCount, "Admin URI [{$uri}] executed {$queryCount} queries (expected <= {$maxQueries}).");
        }

        // Verify monthly transaction report content, descriptions, and formal print document layout
        $this->actingAs($admin)
            ->get('/admin/transactions')
            ->assertStatus(200)
            ->assertSee('Peringkat Event Paling Sering Dipesan')
            ->assertSee('Grafik Tren Pendapatan & Penjualan Tiket Bulanan', false)
            ->assertSee('Ringkasan Akumulasi Transaksi dari Tiap Pemesan')
            ->assertSee('Deskripsi Rinci Setiap Transaksi Pemesan')
            ->assertSee('id="formal-print-document"', false)
            ->assertSee('LAPORAN BULANAN TRANSAKSI, REKAPITULASI PEMESAN & POPULARITAS EVENT', false)
            ->assertSee('I. Ringkasan Eksekutif (Executive Summary)')
            ->assertSee('Pratinjau Dokumen Cetak');

        // Verify parallax, scroll-reveal, and interactive jumbotron motion hooks on public pages
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('id="heritage-scroll-progress"', false)
            ->assertSee('data-parallax-scene', false)
            ->assertSee('data-parallax-speed', false)
            ->assertSee('data-reveal="left"', false)
            ->assertSee('data-tilt-card', false)
            ->assertSee('id="heritage-jumbotron-viewport"', false);
    }
}

