<?php

namespace Database\Seeders;

use App\Models\AdminNotification;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\CompanyProfile;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@pgcandibaru.co.id'],
            [
                'supabase_uid' => 'supa-admin-001',
                'name' => 'Administrator PG Candi Baru',
                'phone' => '0318921002',
                'role' => 'admin',
                'api_token' => 'gulatour-admin-token-demo',
                'password' => Hash::make('password'),
            ]
        );

        $visitor = User::updateOrCreate(
            ['email' => 'wisatawan@example.com'],
            [
                'supabase_uid' => 'supa-user-001',
                'name' => 'Raden Aria Wiratmaja',
                'phone' => '081234567890',
                'role' => 'user',
                'api_token' => 'gulatour-user-token-demo',
                'password' => Hash::make('password'),
            ]
        );

        CompanyProfile::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PT Pabrik Gula Candi Baru',
                'tagline' => 'Warisan Industri Gula Nusantara di Jantung Sidoarjo Sejak 1911',
                'established_year' => 1911,
                'history' => "PT Pabrik Gula Candi Baru berdiri pada tahun 1911 sebagai salah satu tonggak sejarah industri pergulaan di Kabupaten Sidoarjo, Jawa Timur. Sejak masa Hindia Belanda hingga era kemerdekaan dan modernisasi, pabrik ini terus mempertahankan keaslian arsitektur kolonial, mesin uap stasiun giling, serta jalur rel lori pengangkut tebu.\n\nMelalui program GulaTour, PT Pabrik Gula Candi Baru membuka pintu edukasi bagi masyarakat, akademisi, dan komunitas sejarah untuk menyaksikan langsung proses pengolahan tebu menjadi kristal gula murni sekaligus merawat ingatan kolektif warisan industri nasional.",
                'vision' => 'Menjadi perusahaan agroindustri tebu yang berdaya saing berkelanjutan sekaligus pusat edukasi warisan sejarah industri gula terkemuka di Jawa Timur.',
                'mission' => [
                    'Menjaga keandalan operasional produksi gula berkualitas tinggi berbasis kemitraan petani tebu.',
                    'Melestarikan cagar arsitektur kolonial dan artefak mesin uap bersejarah PT Pabrik Gula Candi Baru.',
                    'Menyelenggarakan program kunjungan edukasi dan wisata heritage yang aman, terstruktur, dan informatif bagi publik.',
                ],
                'address' => 'Jl. Raya Candi No. 10, Kecamatan Candi, Kabupaten Sidoarjo, Jawa Timur 61271',
                'phone' => '(031) 8921002',
                'whatsapp' => '628113001911',
                'email' => 'info@pgcandibaru.co.id',
                'operating_hours' => 'Senin – Sabtu, 08.00 – 16.00 WIB (Sesi Tur Sesuai Jadwal Terbuka)',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=112.7050%2C-7.4820%2C112.7200%2C-7.4700&layer=mapnik',
                'hero_image' => '/images/heritage-factory.svg',
            ]
        );

        $galleryItems = [
            [
                'title' => 'Fasad Utama & Cerobong Asap PG Candi Baru',
                'caption' => 'Arsitektur bangunan utama pabrik dengan cerobong bata klasik yang menjadi penanda lanskap Kecamatan Candi.',
                'category' => 'Arsitektur Kolonial',
                'year_label' => 'Era 1911',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Stasiun Gilingan & Roda Gila Mesin Uap',
                'caption' => 'Rangkaian mesin pemerahan nira tebu bertenaga uap yang masih terawat dan digunakan dalam proses produksi.',
                'category' => 'Stasiun Giling',
                'year_label' => 'Koleksi Teknik',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Jalur Lori & Emplasemen Tebu',
                'caption' => 'Jaringan rel lori tempat rangkaian gerbong tebu ditarik menuju timbangan dan meja tebu.',
                'category' => 'Transportasi Lori',
                'year_label' => 'Jalur Rel',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Stasiun Pemurnian, Penguapan & Kristalisasi',
                'caption' => 'Bejana vakum dan ketel masakan tempat nira kental diolah menjadi kristal gula pasir.',
                'category' => 'Proses Produksi',
                'year_label' => 'Edukasi Proses',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($galleryItems as $item) {
            GalleryItem::updateOrCreate(['title' => $item['title']], $item);
        }

        $event1 = Event::updateOrCreate(
            ['slug' => 'tour-pabrik-candi-baru'],
            [
                'title' => 'Tour Heritage & Edukasi Pabrik Gula Candi Baru',
                'description' => 'Kunjungan terpadu menyusuri sejarah pendirian PT Pabrik Gula Candi Baru, pengenalan emplasemen lori tebu, observasi langsung stasiun gilingan mesin uap, hingga stasiun pengemasan kristal gula bersama pemandu teknis pabrik.',
                'highlights' => [
                    'Briefing sejarah & keselamatan kerja (APD helm proyek disediakan)',
                    'Penjelajahan emplasemen rel lori dan timbangan tebu bersejarah',
                    'Observasi Stasiun Gilingan, Pemurnian, Penguapan, dan Putaran Gula',
                    'Sesi diskusi bersama staf sejarah & teknis operasional pabrik',
                ],
                'duration_minutes' => 120,
                'meeting_point' => 'Balai Pertemuan Heritage — Gerbang Utama PG Candi Baru',
                'ticket_price' => 75000,
                'status' => 'active',
            ]
        );

        $event2 = Event::updateOrCreate(
            ['slug' => 'jelajah-arsitektur-dan-mesin-uap'],
            [
                'title' => 'Jelajah Arsitektur Kolonial & Dokumentasi Mesin Uap',
                'description' => 'Sesi khusus bagi komunitas sejarah, arsitektur, dan fotografi untuk mendokumentasikan detail konstruksi baja Eropa awal abad ke-20, roda gila stasiun giling, serta lanskap heritage kawasan pabrik.',
                'highlights' => [
                    'Akses terpandu ke titik pengamatan arsitektur kolonial & rumah dinas bersejarah',
                    'Pemaparan cara kerja mekanisme katup uap dan transmisi roda gigi klasik',
                    'Pendampingan kurator arsip pabrik selama sesi dokumentasi',
                ],
                'duration_minutes' => 150,
                'meeting_point' => 'Pos Utama & Taman Lokomotif PG Candi Baru',
                'ticket_price' => 95000,
                'status' => 'active',
            ]
        );

        // Draft event with TBD price/quota = 0 to demonstrate FR-04 / TASK-12 activation guard
        Event::updateOrCreate(
            ['slug' => 'tur-khusus-musim-giling-tbd'],
            [
                'title' => 'Tur Khusus Pembukaan Musim Giling (Konfigurasi TBD)',
                'description' => 'Program kunjungan spesial pembukaan musim giling. Status masih Draft karena harga tiket dan kuota sesi menunggu ketetapan admin sesuai aturan FR-04.',
                'highlights' => [
                    'Menunggu ketetapan harga tiket tetap dan jadwal sesi oleh Admin',
                ],
                'duration_minutes' => 120,
                'meeting_point' => 'Gerbang Utama PG Candi Baru',
                'ticket_price' => 0,
                'status' => 'draft',
            ]
        );

        $date1 = now()->addDays(3)->startOfDay();
        $date2 = now()->addDays(7)->startOfDay();
        $date3 = now()->addDays(14)->startOfDay();
        $pastDate = now()->subDays(2)->startOfDay();

        $s1 = Schedule::updateOrCreate(
            ['event_id' => $event1->id, 'tour_date' => $date1, 'session_code' => 'PAGI'],
            [
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'quota_total' => 40,
                'quota_booked' => 32,
                'status' => 'open',
            ]
        );

        $s2 = Schedule::updateOrCreate(
            ['event_id' => $event1->id, 'tour_date' => $date1, 'session_code' => 'SIANG'],
            [
                'start_time' => '13:30:00',
                'end_time' => '15:30:00',
                'quota_total' => 30,
                'quota_booked' => 30,
                'status' => 'full',
            ]
        );

        $s3 = Schedule::updateOrCreate(
            ['event_id' => $event1->id, 'tour_date' => $date2, 'session_code' => 'PAGI'],
            [
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'quota_total' => 40,
                'quota_booked' => 6,
                'status' => 'open',
            ]
        );

        $s4 = Schedule::updateOrCreate(
            ['event_id' => $event1->id, 'tour_date' => $date2, 'session_code' => 'SIANG'],
            [
                'start_time' => '13:30:00',
                'end_time' => '15:30:00',
                'quota_total' => 40,
                'quota_booked' => 0,
                'status' => 'open',
            ]
        );

        // Past schedule to demonstrate edge case (expired / unselectable)
        Schedule::updateOrCreate(
            ['event_id' => $event1->id, 'tour_date' => $pastDate, 'session_code' => 'PAGI'],
            [
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'quota_total' => 30,
                'quota_booked' => 28,
                'status' => 'open',
            ]
        );

        Schedule::updateOrCreate(
            ['event_id' => $event2->id, 'tour_date' => $date3, 'session_code' => 'PAGI'],
            [
                'start_time' => '08:30:00',
                'end_time' => '11:00:00',
                'quota_total' => 25,
                'quota_booked' => 5,
                'status' => 'open',
            ]
        );

        $paidOrder = Order::updateOrCreate(
            ['order_code' => 'GT-' . $date1->format('Ymd') . '-DEMO01'],
            [
                'idempotency_key' => 'seed-idem-001',
                'access_token' => 'demo-access-token-paid-001',
                'schedule_id' => $s1->id,
                'user_id' => $visitor->id,
                'buyer_name' => $visitor->name,
                'buyer_email' => $visitor->email,
                'buyer_phone' => (string) $visitor->phone,
                'ticket_qty' => 2,
                'unit_price' => 75000,
                'total_amount' => 150000,
                'status' => 'paid',
                'midtrans_order_id' => 'GT-' . $date1->format('Ymd') . '-DEMO01',
                'midtrans_txn_id' => 'MID-DEMO-SETTLED01',
                'snap_token' => '66e4fa55-demo-settled-01',
                'payment_channel' => 'bank_transfer',
                'paid_at' => now()->subHours(2),
                'expires_at' => now()->addHours(1),
            ]
        );

        app(TicketService::class)->issueTicketsForOrder($paidOrder);

        Order::updateOrCreate(
            ['order_code' => 'GT-' . $date2->format('Ymd') . '-DEMO02'],
            [
                'idempotency_key' => 'seed-idem-002',
                'access_token' => 'demo-access-token-pending-002',
                'schedule_id' => $s3->id,
                'user_id' => null,
                'buyer_name' => 'Siti Aminah (Tamu)',
                'buyer_email' => 'siti.aminah@example.com',
                'buyer_phone' => '081355667788',
                'ticket_qty' => 3,
                'unit_price' => 75000,
                'total_amount' => 225000,
                'status' => 'pending',
                'midtrans_order_id' => 'GT-' . $date2->format('Ymd') . '-DEMO02',
                'snap_token' => '66e4fa55-demo-pending-02',
                'expires_at' => now()->addMinutes(15),
            ]
        );

        AdminNotification::updateOrCreate(
            ['title' => "Pembayaran Masuk: {$paidOrder->order_code}"],
            [
                'type' => 'payment_received',
                'message' => "Pembayaran Rp 150.000 dari {$paidOrder->buyer_name} (2 tiket) telah lunas.",
                'order_id' => $paidOrder->id,
                'is_read' => false,
            ]
        );

        AuditLog::updateOrCreate(
            ['action' => 'event.activated', 'entity_type' => 'Event', 'entity_id' => $event1->id],
            [
                'user_id' => $admin->id,
                'old_values' => ['status' => 'draft', 'ticket_price' => 0],
                'new_values' => ['status' => 'active', 'ticket_price' => 75000],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDay(),
            ]
        );

        $articles = [
            [
                'slug' => 'sejarah-pendirian-pg-candi-baru-1911',
                'title' => 'Jejak Tahun 1911: Berdirinya PT Pabrik Gula Candi Baru di Delta Sidoarjo',
                'category' => 'Arsip & Sejarah',
                'author_name' => 'Tim Kurator Sejarah PG Candi Baru',
                'excerpt' => 'Menelusuri awal mula pembangunan pabrik gula di Kecamatan Candi pada tahun 1911, tata ruang emplasemen kolonial, hingga perannya dalam menopang ekosistem tebu rakyat di Sidoarjo.',
                'content' => "PT Pabrik Gula Candi Baru didirikan pada tahun 1911 di jantung wilayah agraris Kabupaten Sidoarjo, Jawa Timur. Kawasan delta Sungai Brantas yang subur dengan sistem irigasi teknis menjadikan wilayah Candi salah satu sentra perkebunan tebu terbaik sejak awal abad ke-20.\n\nBangunan utama pabrik dirancang dengan konstruksi baja Eropa dan dinding bata tebal berventilasi tinggi guna menjaga sirkulasi udara di sekitar stasiun gilingan serta ketel uap. Cerobong asap bata yang menjulang menjadi penanda orientasi geografis masyarakat sekitar selama lebih dari satu abad.\n\nMelalui pelestarian arsip dan program kunjungan edukasi GulaTour, generasi masa kini dapat mempelajari bagaimana transisi pengelolaan industri gula dari era kolonial menuju perusahaan agroindustri nasional tetap mempertahankan kearifan teknis serta kemitraan erat bersama petani tebu lokal.",
                'reading_time_minutes' => 5,
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
            [
                'slug' => 'cara-kerja-stasiun-gilingan-dan-mesin-uap',
                'title' => 'Harmoni Baja & Tekanan Uap: Mengenal Cara Kerja Stasiun Gilingan Klasik',
                'category' => 'Teknologi Uap',
                'author_name' => 'Divisi Teknik & Pemeliharaan PG Candi Baru',
                'excerpt' => 'Bagaimana batang tebu diperah melalui rangkaian rol gilingan bertenaga uap hingga menghasilkan nira mentah yang siap dimurnikan menjadi kristal gula?',
                'content' => "Salah satu daya tarik utama dalam kunjungan edukasi di PT Pabrik Gula Candi Baru adalah menyaksikan langsung Stasiun Gilingan yang memadukan mekanisme roda gila (flywheel) berdiameter besar dengan transmisi roda gigi presisi.\n\nProses dimulai ketika tebu dari emplasemen dimasukkan ke meja tebu dan dicacah menggunakan pisau pencacah (cane cutter) serta unigrator. Serat tebu yang telah terbuka kemudian melewati lima unit gilingan bertekanan tinggi secara berurutan untuk memisahkan nira dari ampas tebu (bagasse).\n\nMenariknya, ampas tebu hasil pemerahan tidak terbuang sia-sia, melainkan dimanfaatkan kembali sebagai bahan bakar utama ketel uap (boiler) pabrik—sebuah prinsip sirkular energi mandiri yang telah diterapkan sejak seabad silam.",
                'reading_time_minutes' => 4,
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'slug' => 'transformasi-emplasemen-dan-jalur-lori-tebu',
                'title' => 'Jejak Rel Lori Tebu & Tradisi Buka Giling di Sidoarjo',
                'category' => 'Budaya & Komunitas',
                'author_name' => 'Tim Edukasi Heritage GulaTour',
                'excerpt' => 'Tradisi tahunan pembukaan musim giling dan peran vital jaringan rel lori sebagai urat nadi pengangkutan tebu dari lahan petani menuju timbangan pabrik.',
                'content' => "Setiap menjelang musim kemarau, kawasan PT Pabrik Gula Candi Baru bersiap menyambut momentum Buka Giling. Tradisi ini bukan sekadar agenda operasional produksi, melainkan perayaan budaya masyarakat sekitar pabrik dan para petani tebu mitra.\n\nDi halaman emplasemen, jalur rel lori berlebar sepur sempit menjadi saksi bisu bagaimana rangkaian gerbong tebu ditarik masuk menuju stasiun timbangan. Pada sesi kunjungan GulaTour, peserta diajak menyusuri alur kedatangan tebu ini sekaligus memahami standar kendali mutu rendemen sebelum tebu diolah di dalam pabrik.",
                'reading_time_minutes' => 4,
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
        ];

        foreach ($articles as $articleData) {
            Article::updateOrCreate(['slug' => $articleData['slug']], $articleData);
        }
    }
}
