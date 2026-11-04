<?php

namespace Database\Seeders;

use App\Models\AdminNotification;
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

        $date1 = now()->addDays(3)->toDateString();
        $date2 = now()->addDays(7)->toDateString();
        $date3 = now()->addDays(14)->toDateString();
        $pastDate = now()->subDays(2)->toDateString();

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
            ['order_code' => 'GT-' . str_replace('-', '', $date1) . '-DEMO01'],
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
                'midtrans_order_id' => 'GT-' . str_replace('-', '', $date1) . '-DEMO01',
                'midtrans_txn_id' => 'MID-DEMO-SETTLED01',
                'snap_token' => '66e4fa55-demo-settled-01',
                'payment_channel' => 'bank_transfer',
                'paid_at' => now()->subHours(2),
                'expires_at' => now()->addHours(1),
            ]
        );

        app(TicketService::class)->issueTicketsForOrder($paidOrder);

        Order::updateOrCreate(
            ['order_code' => 'GT-' . str_replace('-', '', $date2) . '-DEMO02'],
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
                'midtrans_order_id' => 'GT-' . str_replace('-', '', $date2) . '-DEMO02',
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
    }
}
