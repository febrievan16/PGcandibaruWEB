@extends('layouts.app')

@section('title', $event->title . ' — Pilih Jadwal & Pesan Tiket | GulaTour')

@section('content')
    <!-- Event Header & Uniform Pricing Table -->
    <section class="relative overflow-hidden border-b border-[#E3D9C9] py-10 lg:py-14">
        <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6">
            <div data-reveal="down" class="mb-4">
                <a href="{{ route('events.index') }}" class="link-heritage-tertiary text-xs uppercase tracking-wider">
                    &larr; Kembali ke Katalog Event
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div data-reveal="left" class="lg:col-span-7 space-y-4">
                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <span class="uppercase tracking-[0.08em] font-semibold text-[#4A6B3A]">Event Aktif</span>
                        <span class="text-[#E3D9C9]">|</span>
                        <span class="tabular-nums text-[#6E635A]">Durasi Kunjungan: <strong data-count-up="{{ $event->duration_minutes }}">{{ $event->duration_minutes }}</strong> Menit</span>
                        <span class="text-[#E3D9C9]">|</span>
                        <span class="text-[#6E635A]">{{ $event->meeting_point }}</span>
                    </div>

                    <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-[#2A211A]">
                        {{ $event->title }}
                    </h1>

                    <p class="text-base text-[#6E635A] leading-relaxed max-w-[65ch]">
                        {{ $event->description }}
                    </p>

                    @if(!empty($event->highlights))
                        <div class="pt-2">
                            <h2 class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold mb-2.5">Rangkaian Kegiatan Kunjungan:</h2>
                            <ul data-reveal-stagger="80" class="space-y-1.5 text-sm text-[#2A211A]">
                                @foreach($event->highlights as $highlight)
                                    <li data-reveal="up" class="flex items-start gap-2.5">
                                        <span class="text-[#6B3F1E] font-bold">&bull;</span>
                                        <span>{{ $highlight }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Single-Row Uniform Pricing Table per DESIGN.md §6 & DEC-001 -->
                <div data-reveal="right" data-reveal-delay="140" class="lg:col-span-5 heritage-3d-stage">
                    <div data-tilt-card data-tilt-max="4.5" class="heritage-card-interactive border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-[#E3D9C9] pb-3">
                            <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Informasi Tarif & Ketentuan Tiket</span>
                            <span class="text-xs text-[#6E635A]">Mata Uang: IDR</span>
                        </div>

                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="border-b border-[#E3D9C9] text-left text-xs uppercase tracking-wider text-[#6E635A]">
                                    <th class="py-2 font-semibold">Jenis Tiket</th>
                                    <th class="py-2 font-semibold">Ketentuan</th>
                                    <th class="py-2 font-semibold text-right">Harga Tetap</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-[#E3D9C9]">
                                    <td class="py-3.5 font-semibold text-[#2A211A]">Tiket Peserta Tour</td>
                                    <td class="py-3.5 text-xs text-[#6E635A]">Berlaku seragam tanpa variasi kategori</td>
                                    <td class="py-3.5 text-right font-serif text-xl font-bold text-[#6B3F1E] tabular-nums">
                                        {{ $event->formattedPrice() }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p class="text-xs text-[#6E635A] leading-relaxed">
                            Harga sudah termasuk pemandu resmi pabrik, peminjaman perlengkapan keselamatan (APD), dan E-Ticket ber-QR Code.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2-Step Booking Wizard (DEC-003: Step 1 Schedule First -> Step 2 Buyer Data) -->
    <section class="py-12 pb-24 lg:pb-16">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 space-y-10">

            @if($errors->any())
                <div role="alert" class="border border-[#8B2E1F] bg-[#8B2E1F]/10 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <strong class="text-xs uppercase tracking-wider text-[#8B2E1F] block">Pemesanan Tidak Dapat Diproses</strong>
                        <ul class="text-sm text-[#2A211A] mt-1 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" id="refresh-quota-error-btn" class="btn-heritage-secondary text-xs shrink-0">
                        Muat Ulang Kuota
                    </button>
                </div>
            @endif

            <!-- Wizard Progress Indicator -->
            <div data-reveal="up" class="grid grid-cols-1 sm:grid-cols-2 border border-[#E3D9C9] bg-[#FFFFFF]">
                <div class="p-4 border-b sm:border-b-0 sm:border-r border-[#E3D9C9] flex items-center gap-3 bg-[#F3EAD8]/60">
                    <span class="w-8 h-8 flex items-center justify-center font-serif font-bold text-sm bg-[#6B3F1E] text-[#FAF6EE] rounded-[2px]">1</span>
                    <div>
                        <span class="text-xs uppercase tracking-wider text-[#6B3F1E] font-semibold block">Langkah 1 (Wajib)</span>
                        <span class="font-serif font-semibold text-base text-[#2A211A]">Pilih Tanggal & Sesi Kunjungan</span>
                    </div>
                </div>
                <div class="p-4 flex items-center gap-3 {{ $selectedSchedule ? 'bg-[#F3EAD8]/60' : 'opacity-60' }}">
                    <span class="w-8 h-8 flex items-center justify-center font-serif font-bold text-sm {{ $selectedSchedule ? 'bg-[#6B3F1E] text-[#FAF6EE]' : 'border border-[#E3D9C9] text-[#6E635A]' }} rounded-[2px]">2</span>
                    <div>
                        <span class="text-xs uppercase tracking-wider text-[#6E635A] font-semibold block">Langkah 2</span>
                        <span class="font-serif font-semibold text-base text-[#2A211A]">Isi Data Pemesan & Jumlah Tiket</span>
                    </div>
                </div>
            </div>

            <!-- STEP 1: Timeline Sesi per Tanggal (DESIGN.md §6 & DEC-003) -->
            <div id="wizard-step-1" class="space-y-6">
                <div data-reveal="up" class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E3D9C9] pb-4">
                    <div>
                        <h2 class="font-serif text-2xl font-semibold text-[#2A211A]">
                            Langkah 1: Pilih Tanggal & Sesi Tour
                        </h2>
                        <p class="text-sm text-[#6E635A]">
                            Klik salah satu sesi yang berstatus <strong>Tersedia</strong> untuk membuka formulir pemesanan pada Langkah 2.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span id="quota-live-region" aria-live="polite" class="text-xs text-[#6E635A] tabular-nums">
                            Kuota diperbarui secara real-time
                        </span>
                        <button type="button"
                                id="refresh-quota-btn"
                                data-api-url="{{ route('api.v1.events.schedules', ['event_slug' => $event->slug]) }}"
                                class="btn-heritage-secondary text-xs py-2 px-3 min-h-[44px]">
                            Segarkan Kuota
                        </button>
                    </div>
                </div>

                @if($schedulesByDate->isEmpty())
                    <div data-reveal="scale" class="border border-[#E3D9C9] bg-[#FFFFFF] p-8 text-center">
                        <p class="text-sm text-[#6E635A]">Belum ada sesi jadwal yang dibuka untuk event ini.</p>
                    </div>
                @else
                    <!-- Vertical Session Timeline -->
                    <div data-reveal-stagger="115" class="relative pl-6 sm:pl-8 border-l-2 border-[#E3D9C9] space-y-8">
                        @foreach($schedulesByDate as $dateString => $dateSchedules)
                            @php
                                $firstSchedule = $dateSchedules->first();
                                $isDatePast = $firstSchedule->isPast();
                            @endphp
                            <div data-reveal="up" class="relative">
                                <!-- Timeline Node Dot -->
                                <span class="absolute -left-[31px] sm:-left-[39px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-[#6B3F1E] {{ $isDatePast ? 'bg-[#E3D9C9]' : 'bg-[#C8A24A]' }}"></span>

                                <div class="mb-3 flex flex-wrap items-baseline gap-3">
                                    <h3 class="font-serif text-xl font-bold text-[#2A211A] tabular-nums">
                                        {{ $firstSchedule->tour_date->translatedFormat('l, d F Y') }}
                                    </h3>
                                    @if($isDatePast)
                                        <span class="text-xs uppercase tracking-wider text-[#8B2E1F] font-semibold">Tanggal Lampau (Kedaluwarsa)</span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 heritage-3d-stage">
                                    @foreach($dateSchedules as $schedule)
                                        @php
                                            $isAvailable = $schedule->isAvailable();
                                            $isSelected = ($selectedSchedule && $selectedSchedule->id === $schedule->id);
                                            $remaining = $schedule->remainingQuota();
                                        @endphp
                                        <div data-schedule-card="{{ $schedule->id }}"
                                             @if($isAvailable) data-tilt-card data-tilt-max="4" @endif
                                             class="{{ $isAvailable ? 'heritage-card-interactive' : '' }} border p-4 transition-colors {{ $isSelected ? 'session-card-selected' : 'border-[#E3D9C9] bg-[#FFFFFF]' }} {{ !$isAvailable ? 'opacity-55 bg-[#FAF6EE]' : '' }}">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <span class="text-xs uppercase tracking-[0.06em] font-semibold text-[#6B3F1E] block">
                                                        Sesi {{ $schedule->session_code }}
                                                    </span>
                                                    <span class="font-serif text-lg font-bold text-[#2A211A] tabular-nums">
                                                        {{ $schedule->formattedTimeRange() }} WIB
                                                    </span>
                                                </div>

                                                <div class="text-right tabular-nums">
                                                    <span data-schedule-status="{{ $schedule->id }}"
                                                          class="text-xs font-semibold uppercase tracking-wider block {{ $isAvailable ? 'text-[#4A6B3A]' : 'text-[#8B2E1F]' }}">
                                                        {{ $schedule->statusLabel() }}
                                                    </span>
                                                    <span class="text-xs text-[#6E635A]">
                                                        Sisa Kuota:
                                                        <strong data-schedule-remaining="{{ $schedule->id }}" class="text-[#2A211A]">{{ $remaining }}</strong>
                                                        / {{ $schedule->quota_total }} kursi
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="mt-4 pt-3 border-t border-[#E3D9C9] flex items-center justify-between">
                                                <span class="text-xs text-[#6E635A] tabular-nums">
                                                    Tarif: <strong class="text-[#2A211A]">{{ $event->formattedPrice() }}</strong> / tiket
                                                </span>

                                                @if($isAvailable)
                                                    <a href="{{ route('events.show', ['slug' => $event->slug, 'schedule_id' => $schedule->id]) }}#wizard-step-2"
                                                       class="{{ $isSelected ? 'btn-heritage-primary text-xs py-2 px-4' : 'btn-heritage-secondary text-xs py-2 px-4' }}">
                                                        {{ $isSelected ? 'Sesi Terpilih ✓' : 'Pilih Sesi Ini' }}
                                                    </a>
                                                @else
                                                    <button type="button" disabled class="btn-heritage-secondary text-xs py-2 px-4 opacity-50 cursor-not-allowed">
                                                        {{ $schedule->isPast() ? 'Sesi Berakhir' : 'Kuota Habis' }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- STEP 2: Form Data Diri & Jumlah Tiket (Strictly NOT rendered before Step 1 is valid per DEC-003) -->
            <div id="wizard-step-2" data-reveal="up" class="pt-6 border-t-2 border-[#E3D9C9]">
                @if(!$selectedSchedule)
                    <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-8 text-center space-y-2">
                        <span class="sr-only">Langkah 2 Terkunci (Aturan DEC-003)</span>
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                            Tahap 2: Menunggu Pemilihan Sesi
                        </span>
                        <h3 class="font-serif text-2xl font-semibold text-[#2A211A]">
                            Pilih Tanggal dan Sesi Kunjungan Terlebih Dahulu
                        </h3>
                        <p class="text-sm text-[#6E635A] max-w-[54ch] mx-auto">
                            Formulir data pemesan dan jumlah tiket akan terbuka otomatis begitu Anda mengklik salah satu jadwal sesi yang tersedia pada Langkah 1 di atas.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        <!-- Buyer Details Form -->
                        <div data-reveal="left" class="lg:col-span-7 space-y-6">
                            <div>
                                <span class="text-xs uppercase tracking-[0.08em] text-[#4A6B3A] font-semibold">
                                    Langkah 2 &bull; Jadwal Terpilih
                                </span>
                                <h2 class="font-serif text-2xl font-semibold text-[#2A211A] mt-1">
                                    Lengkapi Data Pemesan & Jumlah Tiket
                                </h2>
                                <p class="text-sm text-[#6E635A] mt-1">
                                    @auth
                                        Anda memesan menggunakan akun terdaftar (<strong>{{ auth()->user()->email }}</strong>). Riwayat dan E-Ticket akan otomatis tersimpan di akun Anda.
                                    @else
                                        Anda dapat langsung memesan sebagai <strong>Tamu (Guest Checkout)</strong> tanpa akun, atau <a href="{{ route('login') }}" class="link-heritage-tertiary">Masuk Akun</a> untuk menyimpan riwayat pemesanan secara otomatis.
                                    @endauth
                                </p>
                            </div>

                            <form id="booking-wizard-form" action="{{ route('events.book', $event->slug) }}" method="POST" class="space-y-5">
                                @csrf
                                <input type="hidden" name="schedule_id" value="{{ $selectedSchedule->id }}">
                                <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

                                <div>
                                    <label for="buyer_name" class="block text-sm font-semibold text-[#2A211A] mb-1.5">
                                        Nama Lengkap Pemesan <span class="text-[#8B2E1F]">*</span>
                                    </label>
                                    <input type="text"
                                           id="buyer_name"
                                           name="buyer_name"
                                           required
                                           value="{{ old('buyer_name', auth()->user()?->name) }}"
                                           placeholder="Contoh: Budi Santoso"
                                           aria-describedby="buyer_name_hint"
                                           class="input-heritage">
                                    <span id="buyer_name_hint" class="text-xs text-[#6E635A] mt-1 block">
                                        Nama ini akan tertera pada lembar E-Ticket resmi.
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label for="buyer_email" class="block text-sm font-semibold text-[#2A211A] mb-1.5">
                                            Alamat Email Aktif <span class="text-[#8B2E1F]">*</span>
                                        </label>
                                        <input type="email"
                                               id="buyer_email"
                                               name="buyer_email"
                                               required
                                               value="{{ old('buyer_email', auth()->user()?->email) }}"
                                               placeholder="nama@email.com"
                                               aria-describedby="buyer_email_hint"
                                               class="input-heritage">
                                        <span id="buyer_email_hint" class="text-xs text-[#6E635A] mt-1 block">
                                            Untuk pengiriman tautan E-Ticket QR.
                                        </span>
                                    </div>

                                    <div>
                                        <label for="buyer_phone" class="block text-sm font-semibold text-[#2A211A] mb-1.5">
                                            Nomor WhatsApp <span class="text-[#8B2E1F]">*</span>
                                        </label>
                                        <input type="tel"
                                               id="buyer_phone"
                                               name="buyer_phone"
                                               required
                                               value="{{ old('buyer_phone', auth()->user()?->phone) }}"
                                               placeholder="081234567890"
                                               aria-describedby="buyer_phone_hint"
                                               class="input-heritage tabular-nums">
                                        <span id="buyer_phone_hint" class="text-xs text-[#6E635A] mt-1 block">
                                            Digunakan untuk notifikasi tiket & koordinasi tur.
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label for="ticket_qty" class="block text-sm font-semibold text-[#2A211A] mb-1.5">
                                        Jumlah Tiket Peserta <span class="text-[#8B2E1F]">*</span>
                                    </label>
                                    <div class="flex items-center gap-4">
                                        <input type="number"
                                               id="ticket_qty"
                                               name="ticket_qty"
                                               min="1"
                                               max="{{ $selectedSchedule->remainingQuota() }}"
                                               value="{{ old('ticket_qty', 1) }}"
                                               required
                                               data-unit-price="{{ $event->ticket_price }}"
                                               aria-describedby="ticket_qty_hint"
                                               class="input-heritage max-w-[140px] tabular-nums font-semibold">
                                        <span id="ticket_qty_hint" class="text-xs text-[#6E635A] tabular-nums">
                                            Maksimal <strong>{{ $selectedSchedule->remainingQuota() }}</strong> tiket sesuai sisa kuota sesi ini.
                                        </span>
                                    </div>
                                </div>

                                <div class="pt-3 hidden sm:block">
                                    <button type="submit" class="btn-heritage-primary w-full sm:w-auto text-base px-8">
                                        Lanjutkan ke Pembayaran &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- 3D Perforated Boarding Pass Order Summary Box -->
                        <div data-reveal="right" data-reveal-delay="120" class="lg:col-span-5 heritage-3d-stage">
                            <div data-tilt-card data-tilt-max="5" class="heritage-card-interactive ticket-3d-perforated p-6 space-y-4">
                                <div class="depth-z-mid flex items-center justify-between gap-2">
                                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                                        Ringkasan Jadwal & Biaya
                                    </span>
                                    <span class="px-2 py-0.5 text-[10px] uppercase tracking-wider font-semibold bg-[#F3EAD8] text-[#6B3F1E] border border-[#C8A24A]">
                                        Boarding Pass 1911
                                    </span>
                                </div>
                                <h3 class="depth-z-front font-serif text-xl font-bold text-[#2A211A]">
                                    {{ $event->title }}
                                </h3>

                                <dl class="divide-y divide-[#E3D9C9] border-t border-b border-dashed border-[#C8A24A] text-sm">
                                    <div class="py-3 flex justify-between gap-4">
                                        <dt class="text-[#6E635A]">Tanggal Kunjungan</dt>
                                        <dd class="font-semibold text-[#2A211A] tabular-nums">{{ $selectedSchedule->tour_date->translatedFormat('d M Y') }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between gap-4">
                                        <dt class="text-[#6E635A]">Sesi & Waktu</dt>
                                        <dd class="font-semibold text-[#2A211A] tabular-nums">{{ $selectedSchedule->session_code }} ({{ $selectedSchedule->formattedTimeRange() }} WIB)</dd>
                                    </div>
                                    <div class="py-3 flex justify-between gap-4">
                                        <dt class="text-[#6E635A]">Sisa Kuota Sesi</dt>
                                        <dd class="font-semibold text-[#4A6B3A] tabular-nums">{{ $selectedSchedule->remainingQuota() }} dari {{ $selectedSchedule->quota_total }} Kursi</dd>
                                    </div>
                                    <div class="py-3 flex justify-between gap-4">
                                        <dt class="text-[#6E635A]">Harga Satuan Tetap</dt>
                                        <dd class="font-semibold text-[#2A211A] tabular-nums">{{ $event->formattedPrice() }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between gap-4">
                                        <dt class="text-[#6E635A]">Jumlah Peserta</dt>
                                        <dd id="summary-qty-display" class="font-semibold text-[#2A211A] tabular-nums">{{ old('ticket_qty', 1) }} Tiket</dd>
                                    </div>
                                </dl>

                                <div class="depth-z-front flex items-baseline justify-between pt-1">
                                    <span class="text-sm font-semibold text-[#2A211A]">Total Pembayaran</span>
                                    <span id="summary-total-display" class="font-serif text-2xl font-bold text-[#6B3F1E] tabular-nums">
                                        Rp {{ number_format($event->ticket_price * (int) old('ticket_qty', 1), 0, ',', '.') }}
                                    </span>
                                </div>

                                <p class="text-xs text-[#6E635A]">
                                    Total biaya dihitung otomatis berdasarkan jumlah peserta dan kuota sesi dikunci saat pesanan dibuat.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Fixed Bottom CTA Bar (< 640px per DESIGN.md §10) -->
                    <div class="sm:hidden fixed bottom-0 inset-x-0 bg-[#FFFFFF] border-t border-[#E3D9C9] p-3 z-40 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] uppercase text-[#6E635A] block">Total Bayar</span>
                            <span id="mobile-total-display" class="font-serif text-lg font-bold text-[#6B3F1E] tabular-nums">
                                Rp {{ number_format($event->ticket_price * (int) old('ticket_qty', 1), 0, ',', '.') }}
                            </span>
                        </div>
                        <button type="submit" form="booking-wizard-form" class="btn-heritage-primary text-sm px-5">
                            Lanjutkan Bayar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const qtyInput = document.getElementById('ticket_qty');
                const summaryQty = document.getElementById('summary-qty-display');
                const summaryTotal = document.getElementById('summary-total-display');
                const mobileTotal = document.getElementById('mobile-total-display');

                function formatIdr(amount) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }

                if (qtyInput) {
                    qtyInput.addEventListener('input', function () {
                        const qty = Math.max(1, parseInt(qtyInput.value || '1', 10));
                        const unitPrice = parseInt(qtyInput.getAttribute('data-unit-price') || '0', 10);
                        const total = qty * unitPrice;
                        if (summaryQty) {
                            summaryQty.textContent = qty + ' Tiket';
                        }
                        if (summaryTotal) {
                            summaryTotal.textContent = formatIdr(total);
                            summaryTotal.classList.remove('price-pulse-active');
                            void summaryTotal.offsetWidth;
                            summaryTotal.classList.add('price-pulse-active');
                        }
                        if (mobileTotal) {
                            mobileTotal.textContent = formatIdr(total);
                        }
                    });
                }

                // Real-time quota refresh button (FR-09)
                const refreshBtn = document.getElementById('refresh-quota-btn');
                const refreshErrBtn = document.getElementById('refresh-quota-error-btn');
                const liveRegion = document.getElementById('quota-live-region');

                async function refreshSchedules() {
                    if (!refreshBtn) return;
                    const url = refreshBtn.getAttribute('data-api-url');
                    if (!url) return;

                    refreshBtn.disabled = true;
                    if (liveRegion) {
                        liveRegion.textContent = 'Memuat ulang sisa kuota...';
                    }

                    try {
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const json = await res.json();
                        if (json.success && json.data && Array.isArray(json.data.schedules)) {
                            json.data.schedules.forEach(function (item) {
                                const remEl = document.querySelector('[data-schedule-remaining="' + item.schedule_id + '"]');
                                const statusEl = document.querySelector('[data-schedule-status="' + item.schedule_id + '"]');
                                if (remEl) {
                                    remEl.textContent = item.remaining;
                                }
                                if (statusEl && item.status_label) {
                                    statusEl.textContent = item.status_label;
                                }
                            });
                            if (liveRegion) {
                                liveRegion.textContent = 'Kuota diperbarui pukul ' + new Date().toLocaleTimeString('id-ID');
                            }
                        }
                    } catch (e) {
                        if (liveRegion) {
                            liveRegion.textContent = 'Gagal memuat kuota, silakan muat ulang halaman.';
                        }
                    } finally {
                        refreshBtn.disabled = false;
                    }
                }

                if (refreshBtn) {
                    refreshBtn.addEventListener('click', refreshSchedules);
                }
                if (refreshErrBtn) {
                    refreshErrBtn.addEventListener('click', function () {
                        window.location.reload();
                    });
                }
            });
        </script>
    @endpush
@endsection

