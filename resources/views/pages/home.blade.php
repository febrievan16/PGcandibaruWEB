@extends('layouts.app')

@section('title', ($profile?->company_name ?? 'PT Pabrik Gula Candi Baru') . ' — GulaTour Heritage & Ticketing')

@section('content')
    @php
        $sliderArticles = $articles ?? collect();
        $totalSlides = 1 + $sliderArticles->count();
    @endphp

    <!-- Interactive Heritage Jumbotron Slider with Multi-Layer Parallax (7/12 Archival Visual + 5/12 Narrative) -->
    <section data-parallax-scene data-parallax-speed="-0.02" class="relative overflow-hidden border-b border-[#E3D9C9] bg-[#FAF6EE]">
        <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6 py-10 lg:py-16">
            <!-- Top Jumbotron Header Bar & Slide Controls -->
            <div data-reveal="down" class="flex flex-wrap items-center justify-between gap-4 pb-5 mb-6 border-b border-[#E3D9C9]">
                <div class="flex items-center gap-3">
                    <span class="inline-block w-2.5 h-2.5 bg-[#C8A24A] border border-[#6B3F1E]"></span>
                    <span id="jumbotron-active-badge" class="text-xs uppercase tracking-[0.08em] font-semibold text-[#6B3F1E]">
                        Slide Utama &bull; Kunjungan Edukasi &amp; Sejarah Resmi
                    </span>
                </div>

                @if($totalSlides > 1)
                    <div class="flex items-center gap-3">
                        <span id="jumbotron-counter" class="text-xs font-semibold text-[#6E635A] tabular-nums">
                            01 / {{ str_pad((string) $totalSlides, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div class="flex items-center gap-1.5" role="group" aria-label="Navigasi Slide Jumbotron">
                            <button type="button"
                                    id="jumbotron-prev-btn"
                                    aria-label="Geser ke slide sebelumnya"
                                    class="w-9 h-9 inline-flex items-center justify-center border border-[#6B3F1E] bg-[#FFFFFF] text-[#6B3F1E] hover:bg-[#F3EAD8] transition-all cursor-pointer">
                                &larr;
                            </button>
                            <button type="button"
                                    id="jumbotron-next-btn"
                                    aria-label="Geser ke slide berikutnya"
                                    class="w-9 h-9 inline-flex items-center justify-center border border-[#6B3F1E] bg-[#FFFFFF] text-[#6B3F1E] hover:bg-[#F3EAD8] transition-all cursor-pointer">
                                &rarr;
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Horizontal Swipeable / Slidable Viewport -->
            <div id="heritage-jumbotron-viewport"
                 class="overflow-hidden select-none cursor-grab active:cursor-grabbing"
                 aria-roledescription="carousel"
                 aria-label="Jumbotron Kunjungan Edukasi dan Ringkasan Artikel Sejarah">
                <div id="heritage-jumbotron-track"
                     class="flex transition-transform duration-500 ease-out"
                     style="transform: translate3d(0%, 0, 0);">

                    <!-- ========================================================================= -->
                    <!-- SLIDE 0: KUNJUNGAN EDUKASI & SEJARAH RESMI (MULTI-LAYER PARALLAX HERO)    -->
                    <!-- ========================================================================= -->
                    <div class="w-full shrink-0"
                         data-jumbotron-slide="0"
                         data-slide-label="Slide Utama &bull; Kunjungan Edukasi &amp; Sejarah Resmi">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                            <!-- 7/12 Archival Factory Illustration & Multi-Layer Parallax Plate -->
                            <div data-reveal="left" class="lg:col-span-7 order-1 lg:order-1">
                                <figure data-tilt-card data-tilt-max="2.5" class="heritage-card-interactive border border-[#E3D9C9] bg-[#FFFFFF] p-3 sm:p-4">
                                    <div class="aspect-[16/10] bg-[#F3EAD8] border border-[#E3D9C9] relative overflow-hidden flex items-center justify-center">
                                        <svg class="w-full h-full object-cover" viewBox="0 0 800 500" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Ilustrasi Arsip Arsitektur PT Pabrik Gula Candi Baru 1911">
                                            <rect width="800" height="500" fill="#F3EAD8"></rect>

                                            <!-- Layer 1: Architectural Blueprint Grid Lines (Deep Background Parallax) -->
                                            <g data-mouse-depth="-8" stroke="#E3D9C9" stroke-width="1">
                                                <line x1="0" y1="100" x2="800" y2="100"></line>
                                                <line x1="0" y1="200" x2="800" y2="200"></line>
                                                <line x1="0" y1="300" x2="800" y2="300"></line>
                                                <line x1="0" y1="410" x2="800" y2="410"></line>
                                                <line x1="160" y1="0" x2="160" y2="500"></line>
                                                <line x1="320" y1="0" x2="320" y2="500"></line>
                                                <line x1="480" y1="0" x2="480" y2="500"></line>
                                                <line x1="640" y1="0" x2="640" y2="500"></line>
                                            </g>

                                            <!-- Layer 2: Rising Steam Puffs from 1911 Smokestack -->
                                            <g data-mouse-depth="18" fill="#FFFFFF" opacity="0.65">
                                                <circle class="steam-puff-1" cx="556" cy="56" r="14"></circle>
                                                <circle class="steam-puff-2" cx="545" cy="42" r="19"></circle>
                                                <circle class="steam-puff-3" cx="564" cy="34" r="16"></circle>
                                            </g>

                                            <!-- Layer 3: Classic Smokestack & Colonial Gable Roofs (Midground Parallax) -->
                                            <g data-mouse-depth="10">
                                                <path d="M540 85 L575 85 L590 410 L525 410 Z" fill="#6B3F1E"></path>
                                                <rect x="533" y="72" width="49" height="14" fill="#2A211A"></rect>
                                                <polygon points="130,240 245,155 360,240" fill="#A67C52" stroke="#6B3F1E" stroke-width="3"></polygon>
                                                <polygon points="320,240 425,165 530,240" fill="#A67C52" stroke="#6B3F1E" stroke-width="3"></polygon>
                                                <rect x="145" y="240" width="370" height="170" fill="#FAF6EE" stroke="#6B3F1E" stroke-width="3"></rect>
                                                <!-- Arched Colonial Windows -->
                                                <g fill="#6B3F1E">
                                                    <rect x="175" y="270" width="36" height="65" rx="18"></rect>
                                                    <rect x="235" y="270" width="36" height="65" rx="18"></rect>
                                                    <rect x="295" y="270" width="36" height="65" rx="18"></rect>
                                                    <rect x="375" y="270" width="36" height="65" rx="18"></rect>
                                                    <rect x="435" y="270" width="36" height="65" rx="18"></rect>
                                                    <rect x="240" y="355" width="75" height="55"></rect>
                                                </g>
                                            </g>

                                            <!-- Layer 4: Rotating Steam Flywheel Emblem (Mechanical Motion + Foreground Depth) -->
                                            <g data-mouse-depth="16">
                                                <g class="flywheel-spin-group">
                                                    <circle cx="660" cy="335" r="55" fill="none" stroke="#6B3F1E" stroke-width="6"></circle>
                                                    <circle cx="660" cy="335" r="14" fill="#C8A24A" stroke="#6B3F1E" stroke-width="3"></circle>
                                                    <line x1="660" y1="280" x2="660" y2="390" stroke="#6B3F1E" stroke-width="3"></line>
                                                    <line x1="605" y1="335" x2="715" y2="335" stroke="#6B3F1E" stroke-width="3"></line>
                                                    <line x1="621" y1="296" x2="699" y2="374" stroke="#A67C52" stroke-width="2"></line>
                                                    <line x1="699" y1="296" x2="621" y2="374" stroke="#A67C52" stroke-width="2"></line>
                                                </g>
                                            </g>

                                            <!-- Layer 5: Lori Rail Track & Archival Plate Caption -->
                                            <g data-mouse-depth="22">
                                                <line x1="40" y1="410" x2="760" y2="410" stroke="#2A211A" stroke-width="4"></line>
                                                <line class="lori-rail-animated" x1="40" y1="422" x2="760" y2="422" stroke="#6B3F1E" stroke-width="2"></line>
                                                <text x="60" y="62" fill="#6B3F1E" font-family="Georgia, serif" font-size="16" font-weight="bold">ARSIP HERITAGE — SIDOARJO, ANNO 1911</text>
                                                <text x="60" y="460" fill="#6E635A" font-family="sans-serif" font-size="13" letter-spacing="2">STASIUN GILINGAN &amp; EMPLASEMEN LORI PT PABRIK GULA CANDI BARU</text>
                                            </g>
                                        </svg>
                                    </div>
                                    <figcaption class="pt-3 flex flex-wrap items-center justify-between text-xs text-[#6E635A] gap-2">
                                        <span>Dok. Arsip: Kompleks Stasiun Giling &amp; Cerobong Bata PT Pabrik Gula Candi Baru (Berdiri <span data-count-up="{{ $profile?->established_year ?? 1911 }}" data-count-from="1885" class="tabular-nums font-semibold text-[#2A211A]">{{ $profile?->established_year ?? 1911 }}</span>)</span>
                                        <span class="uppercase tracking-wider text-[#6B3F1E] font-semibold">Cagar Industri Jawa Timur</span>
                                    </figcaption>
                                </figure>
                            </div>

                            <!-- 5/12 Editorial Narrative & Primary CTA -->
                            <div data-reveal="right" data-reveal-delay="120" class="lg:col-span-5 order-2 lg:order-2 space-y-6">
                                <div class="inline-block border-b border-[#6B3F1E] pb-1">
                                    <span class="text-xs uppercase tracking-[0.08em] font-semibold text-[#6B3F1E]">
                                        Kunjungan Edukasi &amp; Sejarah Resmi
                                    </span>
                                </div>

                                <h1 class="font-serif text-4xl sm:text-5xl font-bold text-[#2A211A] leading-[1.1] tracking-tight">
                                    Menelusuri Jejak Lebih dari Satu Abad Industri Gula Nusantara.
                                </h1>

                                <p class="text-base text-[#6E635A] leading-relaxed">
                                    {{ $profile?->tagline ?? 'Warisan Industri Gula Nusantara di Jantung Sidoarjo Sejak 1911.' }}
                                    Saksikan langsung mekanisme roda gila mesin uap kolonial, jalur rel lori tebu, hingga proses kristalisasi gula bersama pemandu operasional pabrik.
                                </p>

                                <div class="pt-2 flex flex-wrap items-center gap-4">
                                    <a href="#jadwal-event" class="btn-heritage-primary">
                                        Pesan Tiket Tour
                                    </a>
                                    <a href="{{ route('profile') }}" class="btn-heritage-secondary">
                                        Pelajari Sejarah Pabrik
                                    </a>
                                </div>

                                <dl class="pt-6 border-t border-[#E3D9C9] grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <dt class="text-xs uppercase tracking-wider text-[#6E635A]">Model Tarif Tiket</dt>
                                        <dd class="font-semibold text-[#2A211A] mt-0.5 tabular-nums">Harga Tetap / Peserta</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase tracking-wider text-[#6E635A]">Alur Pemesanan</dt>
                                        <dd class="font-semibold text-[#2A211A] mt-0.5">Pilih Sesi &rarr; E-Ticket QR</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- SLIDES 1..N: SWIPEABLE ARTICLE SUMMARIES IN HERO JUMBOTRON                -->
                    <!-- ========================================================================= -->
                    @foreach($sliderArticles as $idx => $article)
                        <div class="w-full shrink-0"
                             data-jumbotron-slide="{{ $idx + 1 }}"
                             data-slide-label="Ringkasan Artikel Heritage #{{ $idx + 1 }} &bull; {{ e($article->category) }}">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                                <!-- 7/12 Article Visual Plate -->
                                <div class="lg:col-span-7 order-1 lg:order-1">
                                    <figure data-tilt-card data-tilt-max="2.5" class="group heritage-card-interactive border border-[#E3D9C9] bg-[#FFFFFF] p-3 sm:p-4">
                                        <div class="aspect-[16/10] bg-[#F3EAD8] border border-[#E3D9C9] relative overflow-hidden flex items-center justify-center">
                                            @if($article->cover_image)
                                                <img src="{{ $article->cover_image }}"
                                                     alt="{{ $article->title }}"
                                                     loading="lazy"
                                                     class="heritage-img-zoom w-full h-full object-cover">
                                            @else
                                                <svg class="w-full h-full object-cover" viewBox="0 0 800 500" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ $article->title }}">
                                                    <rect width="800" height="500" fill="#F3EAD8"></rect>
                                                    <g stroke="#E3D9C9" stroke-width="1">
                                                        <line x1="0" y1="100" x2="800" y2="100"></line>
                                                        <line x1="0" y1="400" x2="800" y2="400"></line>
                                                        <line x1="120" y1="0" x2="120" y2="500"></line>
                                                        <line x1="680" y1="0" x2="680" y2="500"></line>
                                                    </g>
                                                    <rect x="60" y="55" width="680" height="390" fill="#FAF6EE" stroke="#6B3F1E" stroke-width="3"></rect>
                                                    <rect x="78" y="73" width="644" height="354" fill="none" stroke="#C8A24A" stroke-width="1.5"></rect>
                                                    <text x="400" y="160" text-anchor="middle" fill="#A67C52" font-family="sans-serif" font-size="14" font-weight="bold" letter-spacing="3">PUBLIKASI &amp; CATATAN SEJARAH RESMI</text>
                                                    <text x="400" y="235" text-anchor="middle" fill="#6B3F1E" font-family="Georgia, serif" font-size="28" font-weight="bold">{{ strtoupper($article->category) }}</text>
                                                    <line x1="260" y1="265" x2="540" y2="265" stroke="#6B3F1E" stroke-width="2"></line>
                                                    <text x="400" y="310" text-anchor="middle" fill="#2A211A" font-family="Georgia, serif" font-size="18">PT Pabrik Gula Candi Baru — Sidoarjo (1911)</text>
                                                    <text x="400" y="365" text-anchor="middle" fill="#6E635A" font-family="sans-serif" font-size="13">Oleh {{ $article->author_name }} &bull; Estimasi Baca {{ $article->reading_time_minutes }} Menit</text>
                                                </svg>
                                            @endif
                                        </div>
                                        <figcaption class="pt-3 flex flex-wrap items-center justify-between text-xs text-[#6E635A] gap-2">
                                            <span>Arsip Artikel: <strong class="text-[#2A211A]">{{ $article->author_name }}</strong> ({{ ($article->published_at ?? $article->created_at)?->translatedFormat('d M Y') }})</span>
                                            <span class="uppercase tracking-wider text-[#6B3F1E] font-semibold">Waktu Baca {{ $article->reading_time_minutes }} Menit</span>
                                        </figcaption>
                                    </figure>
                                </div>

                                <!-- 5/12 Article Summary & Read CTA -->
                                <div class="lg:col-span-5 order-2 lg:order-2 space-y-5">
                                    <div class="inline-block border-b border-[#6B3F1E] pb-1">
                                        <span class="text-xs uppercase tracking-[0.08em] font-semibold text-[#6B3F1E]">
                                            Ringkasan Artikel &bull; {{ $article->category }}
                                        </span>
                                    </div>

                                    <h2 class="font-serif text-3xl sm:text-4xl font-bold text-[#2A211A] leading-[1.15] tracking-tight">
                                        {{ $article->title }}
                                    </h2>

                                    <p class="text-base text-[#6E635A] leading-relaxed">
                                        {{ $article->excerpt }}
                                    </p>

                                    <div class="pt-2 flex flex-wrap items-center gap-4">
                                        <a href="{{ route('articles.show', $article->slug) }}" class="btn-heritage-primary">
                                            Baca Artikel Lengkap &rarr;
                                        </a>
                                        <a href="{{ route('articles.index') }}" class="btn-heritage-secondary">
                                            Semua Artikel ({{ $sliderArticles->count() }})
                                        </a>
                                    </div>

                                    <dl class="pt-5 border-t border-[#E3D9C9] grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <dt class="text-xs uppercase tracking-wider text-[#6E635A]">Penulis / Kurator</dt>
                                            <dd class="font-semibold text-[#2A211A] mt-0.5">{{ $article->author_name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs uppercase tracking-wider text-[#6E635A]">Tanggal Terbit</dt>
                                            <dd class="font-semibold text-[#2A211A] mt-0.5 tabular-nums">{{ ($article->published_at ?? $article->created_at)?->translatedFormat('d M Y') }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            <!-- Bottom Jumbotron Slide Dots / Quick Jump Pills -->
            @if($totalSlides > 1)
                <div class="pt-6 mt-6 border-t border-[#E3D9C9] flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Pilih Slide Jumbotron">
                        <button type="button"
                                data-jumbotron-dot="0"
                                class="px-3 py-1.5 text-xs font-semibold border border-[#6B3F1E] bg-[#6B3F1E] text-[#FAF6EE] transition-colors cursor-pointer">
                            1. Kunjungan Edukasi &amp; Sejarah
                        </button>
                        @foreach($sliderArticles as $idx => $article)
                            <button type="button"
                                    data-jumbotron-dot="{{ $idx + 1 }}"
                                    class="px-3 py-1.5 text-xs font-medium border border-[#E3D9C9] bg-[#FFFFFF] text-[#2A211A] hover:border-[#6B3F1E] transition-colors cursor-pointer max-w-[220px] truncate">
                                {{ $idx + 2 }}. {{ $article->title }}
                            </button>
                        @endforeach
                    </div>
                    <span class="text-xs text-[#6E635A]">
                        Geser ke kanan/kiri atau gunakan tombol panah untuk menelusuri ringkasan artikel
                    </span>
                </div>
            @endif
        </div>
    </section>

    <!-- Active Event Catalog & Uniform Pricing Table -->
    <section id="jadwal-event" class="relative overflow-hidden border-b border-[#E3D9C9] py-14 lg:py-20">
        <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6">
            <div data-reveal="up" class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8 border-b border-[#E3D9C9]">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Katalog Event &amp; Jadwal Terbuka</span>
                    <h2 class="font-serif text-3xl font-semibold text-[#2A211A] mt-1">Program Kunjungan &amp; Sesi Tour Aktif</h2>
                </div>
                <p class="text-sm text-[#6E635A] max-w-[46ch]">
                    Pilih tanggal dan sesi kunjungan terlebih dahulu sebelum mengisi data pemesan. Seluruh harga tiket berlaku seragam per peserta.
                </p>
            </div>

            @if($events->isEmpty())
                <!-- Empty State per DESIGN.md §11 -->
                <div data-reveal="scale" class="py-12 border-b border-[#E3D9C9] text-center space-y-4">
                    <p class="font-serif text-xl text-[#2A211A]">Belum ada event tour aktif.</p>
                    <p class="text-sm text-[#6E635A]">Event akan tampil setelah admin mengaktifkan jadwal.</p>
                    <div>
                        <a href="{{ route('profile') }}" class="btn-heritage-secondary">Lihat Profil Perusahaan</a>
                    </div>
                </div>
            @else
                <div data-reveal-stagger="130" class="divide-y divide-[#E3D9C9]">
                    @foreach($events as $event)
                        @php
                            $openSchedules = $event->schedules->filter(fn($s) => $s->isAvailable());
                            $totalRemaining = $openSchedules->sum(fn($s) => $s->remainingQuota());
                        @endphp
                        <article data-reveal="up" class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            <!-- Small Archival Thumbnail + Meta (List Item Pattern per DESIGN.md §6 & §8) -->
                            <div class="lg:col-span-3">
                                <div data-tilt-card class="heritage-card-interactive aspect-[4/3] bg-[#F3EAD8] border border-[#E3D9C9] p-4 flex flex-col justify-between">
                                    <span class="text-[11px] uppercase tracking-[0.08em] text-[#6B3F1E] font-semibold tabular-nums">
                                        Durasi <span data-count-up="{{ $event->duration_minutes }}">{{ $event->duration_minutes }}</span> Menit
                                    </span>
                                    <div class="my-auto py-2">
                                        <span class="font-serif text-lg font-semibold text-[#2A211A] block leading-snug">
                                            {{ $event->title }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-[#6E635A] border-t border-[#E3D9C9] pt-2">
                                        Titik Kumpul: {{ $event->meeting_point }}
                                    </span>
                                </div>
                            </div>

                            <!-- Event Narrative & Highlights -->
                            <div class="lg:col-span-6 space-y-3">
                                <div class="flex flex-wrap items-center gap-3 text-xs">
                                    <span class="uppercase tracking-wider font-semibold text-[#4A6B3A]">Status: Event Aktif</span>
                                    <span class="text-[#E3D9C9]">&bull;</span>
                                    <span class="tabular-nums text-[#6E635A]">{{ $openSchedules->count() }} Sesi Tersedia</span>
                                    <span class="text-[#E3D9C9]">&bull;</span>
                                    <span class="tabular-nums text-[#2A211A] font-medium">
                                        Total Sisa Kuota: <strong data-count-up="{{ $totalRemaining }}">{{ $totalRemaining }}</strong> Kursi
                                    </span>
                                </div>

                                <h3 class="font-serif text-2xl font-semibold text-[#2A211A]">
                                    <a href="{{ route('events.show', $event->slug) }}" class="hover:text-[#6B3F1E] transition-colors">
                                        {{ $event->title }}
                                    </a>
                                </h3>

                                <p class="text-sm text-[#6E635A] leading-relaxed max-w-[65ch]">
                                    {{ $event->description }}
                                </p>

                                <!-- Upcoming Session Timeline Pills -->
                                @if($event->schedules->isNotEmpty())
                                    <div class="pt-2">
                                        <p class="text-xs uppercase tracking-wider text-[#6E635A] mb-2">Jadwal Sesi Terdekat (Klik untuk Memilih):</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($event->schedules->take(4) as $schedule)
                                                @if($schedule->isAvailable())
                                                    <a href="{{ route('events.show', ['slug' => $event->slug, 'schedule_id' => $schedule->id]) }}#wizard-step-2"
                                                       class="heritage-card-interactive px-3 py-1.5 text-xs border border-[#E3D9C9] bg-[#FFFFFF] hover:border-[#6B3F1E] hover:bg-[#F3EAD8] transition-all tabular-nums">
                                                        <strong class="text-[#2A211A]">{{ $schedule->tour_date->translatedFormat('d M Y') }}</strong>
                                                        <span class="text-[#6E635A]">({{ $schedule->formattedTimeRange() }})</span>
                                                        <span class="text-[#4A6B3A] font-semibold ml-1">&bull; Sisa {{ $schedule->remainingQuota() }}</span>
                                                    </a>
                                                @else
                                                    <span class="px-3 py-1.5 text-xs border border-[#E3D9C9] bg-[#FAF6EE] text-[#6E635A] opacity-60 tabular-nums">
                                                        {{ $schedule->tour_date->translatedFormat('d M Y') }} ({{ $schedule->formattedTimeRange() }}) &bull; {{ $schedule->statusLabel() }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Uniform Flat Price Box & Action (DEC-001) -->
                            <div class="lg:col-span-3 lg:border-l lg:border-[#E3D9C9] lg:pl-6 flex flex-col justify-between space-y-4">
                                <div data-parallax-speed="-0.04">
                                    <span class="text-xs uppercase tracking-wider text-[#6E635A] block">Harga Tiket Tetap</span>
                                    <span class="font-serif text-2xl font-bold text-[#6B3F1E] tabular-nums block mt-0.5">
                                        {{ $event->formattedPrice() }}
                                    </span>
                                    <span class="text-xs text-[#6E635A]">Per peserta (berlaku seragam semua kategori)</span>
                                </div>

                                <a href="{{ route('events.show', $event->slug) }}" class="btn-heritage-secondary w-full text-center">
                                    Pilih Tanggal &amp; Sesi &rarr;
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- M1 Summary: Editorial History & Vision-Mission Columns (No Cards per DESIGN.md §8) -->
    @if($profile)
        <section class="relative overflow-hidden py-14 lg:py-20 border-b border-[#E3D9C9]">
            <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                    <div class="lg:col-span-6 space-y-4">
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">
                            Sekilas Sejarah (Berdiri <span class="tabular-nums font-semibold">{{ $profile->established_year }}</span>)
                        </span>
                        <h2 class="font-serif text-3xl font-semibold text-[#2A211A]">{{ $profile->company_name }}</h2>
                        <div class="space-y-4 text-base text-[#2A211A] leading-relaxed max-w-[65ch]">
                            @foreach(explode("\n\n", $profile->history) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('profile') }}" class="link-heritage-tertiary text-sm">
                                Baca Profil Lengkap, Galeri Arsip &amp; Kontak Operasional &rarr;
                            </a>
                        </div>
                    </div>

                    <div data-reveal="right" data-reveal-delay="140" class="lg:col-span-6 lg:border-l lg:border-[#E3D9C9] lg:pl-10 space-y-6">
                        <div class="pb-6 border-b border-[#E3D9C9]">
                            <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block mb-2">Visi Perusahaan</span>
                            <p class="font-serif text-xl text-[#2A211A] leading-snug">
                                “{{ $profile->vision }}”
                            </p>
                        </div>

                        <div>
                            <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block mb-3">Misi Pelestarian &amp; Operasional</span>
                            <ol data-reveal-stagger="95" class="space-y-3 text-sm text-[#2A211A]">
                                @foreach($profile->mission ?? [] as $index => $missionItem)
                                    <li data-reveal="up" class="flex items-start gap-3">
                                        <span class="font-serif font-bold text-[#6B3F1E] tabular-nums">0{{ $index + 1 }}.</span>
                                        <span class="leading-relaxed">{{ $missionItem }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const track = document.getElementById('heritage-jumbotron-track');
                const viewport = document.getElementById('heritage-jumbotron-viewport');
                const slides = document.querySelectorAll('[data-jumbotron-slide]');
                const dots = document.querySelectorAll('[data-jumbotron-dot]');
                const prevBtn = document.getElementById('jumbotron-prev-btn');
                const nextBtn = document.getElementById('jumbotron-next-btn');
                const counterEl = document.getElementById('jumbotron-counter');
                const badgeEl = document.getElementById('jumbotron-active-badge');

                if (!track || slides.length <= 1) {
                    return;
                }

                let currentSlide = 0;
                const total = slides.length;
                let dragStartX = null;

                function goToSlide(index) {
                    currentSlide = (index + total) % total;
                    track.style.transform = 'translate3d(-' + (currentSlide * 100) + '%, 0, 0)';

                    if (counterEl) {
                        const num = String(currentSlide + 1).padStart(2, '0');
                        const max = String(total).padStart(2, '0');
                        counterEl.textContent = num + ' / ' + max;
                    }

                    const activeSlideEl = slides[currentSlide];
                    if (badgeEl && activeSlideEl) {
                        badgeEl.innerHTML = activeSlideEl.getAttribute('data-slide-label') || '';
                    }

                    dots.forEach(function (dot, idx) {
                        if (idx === currentSlide) {
                            dot.classList.add('border-[#6B3F1E]', 'bg-[#6B3F1E]', 'text-[#FAF6EE]', 'font-semibold');
                            dot.classList.remove('border-[#E3D9C9]', 'bg-[#FFFFFF]', 'text-[#2A211A]');
                        } else {
                            dot.classList.remove('border-[#6B3F1E]', 'bg-[#6B3F1E]', 'text-[#FAF6EE]', 'font-semibold');
                            dot.classList.add('border-[#E3D9C9]', 'bg-[#FFFFFF]', 'text-[#2A211A]');
                        }
                    });
                }

                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        goToSlide(currentSlide - 1);
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        goToSlide(currentSlide + 1);
                    });
                }

                dots.forEach(function (dot) {
                    dot.addEventListener('click', function () {
                        const idx = parseInt(dot.getAttribute('data-jumbotron-dot') || '0', 10);
                        goToSlide(idx);
                    });
                });

                if (viewport) {
                    viewport.addEventListener('touchstart', function (e) {
                        if (e.touches && e.touches.length === 1) {
                            dragStartX = e.touches[0].clientX;
                        }
                    }, { passive: true });

                    viewport.addEventListener('touchend', function (e) {
                        if (dragStartX === null || !e.changedTouches || !e.changedTouches.length) {
                            return;
                        }
                        const deltaX = e.changedTouches[0].clientX - dragStartX;
                        if (Math.abs(deltaX) > 45) {
                            goToSlide(deltaX < 0 ? currentSlide + 1 : currentSlide - 1);
                        }
                        dragStartX = null;
                    }, { passive: true });

                    viewport.addEventListener('mousedown', function (e) {
                        dragStartX = e.clientX;
                    });

                    viewport.addEventListener('mouseup', function (e) {
                        if (dragStartX === null) {
                            return;
                        }
                        const deltaX = e.clientX - dragStartX;
                        if (Math.abs(deltaX) > 55) {
                            goToSlide(deltaX < 0 ? currentSlide + 1 : currentSlide - 1);
                        }
                        dragStartX = null;
                    });
                }
            });
        </script>
    @endpush
@endsection
