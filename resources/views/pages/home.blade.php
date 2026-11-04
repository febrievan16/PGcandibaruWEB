@extends('layouts.app')

@section('title', ($profile?->company_name ?? 'PT Pabrik Gula Candi Baru') . ' — GulaTour Heritage & Ticketing')

@section('content')
    <!-- Editorial Split Hero (7/12 Archival Visual + 5/12 Narrative per DESIGN.md §6) -->
    <section class="border-b border-[#E3D9C9]">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-10 lg:py-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <!-- 7/12 Archival Factory Illustration & Plate -->
                <div class="lg:col-span-7 order-1 lg:order-1">
                    <figure class="border border-[#E3D9C9] bg-[#FFFFFF] p-3 sm:p-4">
                        <div class="aspect-[16/10] bg-[#F3EAD8] border border-[#E3D9C9] relative overflow-hidden flex items-center justify-center">
                            <svg class="w-full h-full object-cover" viewBox="0 0 800 500" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Ilustrasi Arsip Arsitektur PT Pabrik Gula Candi Baru 1911">
                                <rect width="800" height="500" fill="#F3EAD8"/>
                                <!-- Architectural Grid Lines -->
                                <g stroke="#E3D9C9" stroke-width="1">
                                    <line x1="0" y1="100" x2="800" y2="100"/>
                                    <line x1="0" y1="200" x2="800" y2="200"/>
                                    <line x1="0" y1="300" x2="800" y2="300"/>
                                    <line x1="0" y1="410" x2="800" y2="410"/>
                                    <line x1="160" y1="0" x2="160" y2="500"/>
                                    <line x1="320" y1="0" x2="320" y2="500"/>
                                    <line x1="480" y1="0" x2="480" y2="500"/>
                                    <line x1="640" y1="0" x2="640" y2="500"/>
                                </g>
                                <!-- Classic Smokestack & Mill Silhouette -->
                                <path d="M540 85 L575 85 L590 410 L525 410 Z" fill="#6B3F1E"/>
                                <rect x="533" y="72" width="49" height="14" fill="#2A211A"/>
                                <!-- Main Colonial Gable Roofs -->
                                <polygon points="130,240 245,155 360,240" fill="#A67C52" stroke="#6B3F1E" stroke-width="3"/>
                                <polygon points="320,240 425,165 530,240" fill="#A67C52" stroke="#6B3F1E" stroke-width="3"/>
                                <rect x="145" y="240" width="370" height="170" fill="#FAF6EE" stroke="#6B3F1E" stroke-width="3"/>
                                <!-- Arched Colonial Windows -->
                                <g fill="#6B3F1E">
                                    <rect x="175" y="270" width="36" height="65" rx="18"/>
                                    <rect x="235" y="270" width="36" height="65" rx="18"/>
                                    <rect x="295" y="270" width="36" height="65" rx="18"/>
                                    <rect x="375" y="270" width="36" height="65" rx="18"/>
                                    <rect x="435" y="270" width="36" height="65" rx="18"/>
                                    <rect x="240" y="355" width="75" height="55"/>
                                </g>
                                <!-- Steam Flywheel Emblem -->
                                <circle cx="660" cy="335" r="55" fill="none" stroke="#6B3F1E" stroke-width="6"/>
                                <circle cx="660" cy="335" r="14" fill="#C8A24A" stroke="#6B3F1E" stroke-width="3"/>
                                <line x1="660" y1="280" x2="660" y2="390" stroke="#6B3F1E" stroke-width="3"/>
                                <line x1="605" y1="335" x2="715" y2="335" stroke="#6B3F1E" stroke-width="3"/>
                                <!-- Lori Rail Track -->
                                <line x1="40" y1="410" x2="760" y2="410" stroke="#2A211A" stroke-width="4"/>
                                <line x1="40" y1="422" x2="760" y2="422" stroke="#6B3F1E" stroke-width="2"/>
                                <!-- Archival Plate Caption Inside SVG -->
                                <text x="60" y="62" fill="#6B3F1E" font-family="Georgia, serif" font-size="16" font-weight="bold">ARSIP HERITAGE — SIDOARJO, ANNO 1911</text>
                                <text x="60" y="460" fill="#6E635A" font-family="sans-serif" font-size="13" letter-spacing="2">STASIUN GILINGAN &amp; EMPLASEMEN LORI PT PABRIK GULA CANDI BARU</text>
                            </svg>
                        </div>
                        <figcaption class="pt-3 flex flex-wrap items-center justify-between text-xs text-[#6E635A] gap-2">
                            <span>Dok. Arsip: Kompleks Stasiun Giling & Cerobong Bata PT Pabrik Gula Candi Baru (Berdiri {{ $profile?->established_year ?? 1911 }})</span>
                            <span class="uppercase tracking-wider text-[#6B3F1E] font-semibold">Cagar Industri Jawa Timur</span>
                        </figcaption>
                    </figure>
                </div>

                <!-- 5/12 Editorial Narrative & Primary CTA -->
                <div class="lg:col-span-5 order-2 lg:order-2 space-y-6">
                    <div class="inline-block border-b border-[#6B3F1E] pb-1">
                        <span class="text-xs uppercase tracking-[0.08em] font-semibold text-[#6B3F1E]">
                            Kunjungan Edukasi & Sejarah Resmi
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
    </section>

    <!-- M2: Active Event Catalog & Uniform Pricing Table (List Layout per DESIGN.md §6) -->
    <section id="jadwal-event" class="border-b border-[#E3D9C9] py-14 lg:py-20">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8 border-b border-[#E3D9C9]">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Katalog Event & Jadwal Terbuka</span>
                    <h2 class="font-serif text-3xl font-semibold text-[#2A211A] mt-1">Program Kunjungan & Sesi Tour Aktif</h2>
                </div>
                <p class="text-sm text-[#6E635A] max-w-[46ch]">
                    Pilih tanggal dan sesi kunjungan terlebih dahulu sebelum mengisi data pemesan. Seluruh harga tiket berlaku seragam per peserta.
                </p>
            </div>

            @if($events->isEmpty())
                <!-- Empty State per DESIGN.md §11 -->
                <div class="py-12 border-b border-[#E3D9C9] text-center space-y-4">
                    <p class="font-serif text-xl text-[#2A211A]">Belum ada event tour aktif.</p>
                    <p class="text-sm text-[#6E635A]">Event akan tampil setelah admin mengaktifkan jadwal.</p>
                    <div>
                        <a href="{{ route('profile') }}" class="btn-heritage-secondary">Lihat Profil Perusahaan</a>
                    </div>
                </div>
            @else
                <div class="divide-y divide-[#E3D9C9]">
                    @foreach($events as $event)
                        @php
                            $openSchedules = $event->schedules->filter(fn($s) => $s->isAvailable());
                            $totalRemaining = $openSchedules->sum(fn($s) => $s->remainingQuota());
                        @endphp
                        <article class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            <!-- Small Archival Thumbnail + Meta (List Item Pattern per DESIGN.md §6 & §8) -->
                            <div class="lg:col-span-3">
                                <div class="aspect-[4/3] bg-[#F3EAD8] border border-[#E3D9C9] p-4 flex flex-col justify-between">
                                    <span class="text-[11px] uppercase tracking-[0.08em] text-[#6B3F1E] font-semibold">
                                        Durasi {{ $event->duration_minutes }} Menit
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
                                    <span class="tabular-nums text-[#2A211A] font-medium">Total Sisa Kuota: {{ $totalRemaining }} Kursi</span>
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
                                                       class="px-3 py-1.5 text-xs border border-[#E3D9C9] bg-[#FFFFFF] hover:border-[#6B3F1E] hover:bg-[#F3EAD8] transition-colors tabular-nums">
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
                                <div>
                                    <span class="text-xs uppercase tracking-wider text-[#6E635A] block">Harga Tiket Tetap</span>
                                    <span class="font-serif text-2xl font-bold text-[#6B3F1E] tabular-nums block mt-0.5">
                                        {{ $event->formattedPrice() }}
                                    </span>
                                    <span class="text-xs text-[#6E635A]">Per peserta (berlaku seragam semua kategori)</span>
                                </div>

                                <a href="{{ route('events.show', $event->slug) }}" class="btn-heritage-secondary w-full text-center">
                                    Pilih Tanggal & Sesi &rarr;
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
        <section class="py-14 lg:py-20 border-b border-[#E3D9C9]">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                    <div class="lg:col-span-6 space-y-4">
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Sekilas Sejarah (Berdiri {{ $profile->established_year }})</span>
                        <h2 class="font-serif text-3xl font-semibold text-[#2A211A]">{{ $profile->company_name }}</h2>
                        <div class="space-y-4 text-base text-[#2A211A] leading-relaxed max-w-[65ch]">
                            @foreach(explode("\n\n", $profile->history) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('profile') }}" class="link-heritage-tertiary text-sm">
                                Baca Profil Lengkap, Galeri Arsip & Kontak Operasional &rarr;
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-6 lg:border-l lg:border-[#E3D9C9] lg:pl-10 space-y-6">
                        <div class="pb-6 border-b border-[#E3D9C9]">
                            <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block mb-2">Visi Perusahaan</span>
                            <p class="font-serif text-xl text-[#2A211A] leading-snug">
                                “{{ $profile->vision }}”
                            </p>
                        </div>

                        <div>
                            <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block mb-3">Misi Pelestarian & Operasional</span>
                            <ol class="space-y-3 text-sm text-[#2A211A]">
                                @foreach($profile->mission ?? [] as $index => $missionItem)
                                    <li class="flex items-start gap-3">
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
@endsection

