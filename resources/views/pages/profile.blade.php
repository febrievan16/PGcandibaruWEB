@extends('layouts.app')

@section('title', 'Profil & Sejarah Perusahaan — ' . ($profile?->company_name ?? 'PT Pabrik Gula Candi Baru'))

@section('content')
    <!-- Editorial Header -->
    <section class="border-b border-[#E3D9C9] py-12 lg:py-16">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
            <div class="max-w-[68ch] space-y-4">
                <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">
                    Modul M1 &bull; Profil & Warisan Sejarah (Sejak {{ $profile?->established_year ?? 1911 }})
                </span>
                <h1 class="font-serif text-4xl sm:text-5xl font-bold text-[#2A211A]">
                    {{ $profile?->company_name ?? 'PT Pabrik Gula Candi Baru' }}
                </h1>
                <p class="text-lg text-[#6E635A] leading-relaxed">
                    {{ $profile?->tagline ?? 'Warisan Industri Gula Nusantara di Jantung Sidoarjo Sejak 1911.' }}
                </p>
            </div>
        </div>
    </section>

    <!-- Sejarah & Visi Misi (Editorial Typography without Card Wrappers per DESIGN.md §8) -->
    <section class="border-b border-[#E3D9C9] py-12 lg:py-16">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div class="lg:col-span-7 space-y-5">
                <h2 class="font-serif text-2xl sm:text-3xl font-semibold text-[#6B3F1E]">
                    Sejarah Pendirian & Perjalanan Industri
                </h2>
                <div class="space-y-4 text-base text-[#2A211A] leading-relaxed max-w-[68ch]">
                    @if($profile?->history)
                        @foreach(explode("\n\n", $profile->history) as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    @else
                        <p>PT Pabrik Gula Candi Baru berdiri sejak tahun 1911 di Kabupaten Sidoarjo, Jawa Timur.</p>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5 lg:border-l lg:border-[#E3D9C9] lg:pl-10 space-y-8">
                <div class="pb-8 border-b border-[#E3D9C9]">
                    <h2 class="font-serif text-2xl font-semibold text-[#6B3F1E] mb-3">Visi</h2>
                    <p class="font-serif text-lg text-[#2A211A] leading-relaxed">
                        “{{ $profile?->vision }}”
                    </p>
                </div>

                <div>
                    <h2 class="font-serif text-2xl font-semibold text-[#6B3F1E] mb-4">Misi</h2>
                    <ul class="space-y-4">
                        @foreach($profile?->mission ?? [] as $idx => $item)
                            <li class="flex items-start gap-3 pb-3 border-b border-[#E3D9C9] last:border-b-0">
                                <span class="font-serif text-sm font-bold text-[#A67C52] tabular-nums">0{{ $idx + 1 }}</span>
                                <span class="text-sm text-[#2A211A] leading-relaxed">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Archival Gallery with Lazy Loading & Heritage Placeholder Edge Case (FEATURES.md §2) -->
    <section class="border-b border-[#E3D9C9] py-12 lg:py-16">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 space-y-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-[#E3D9C9] pb-5">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Dokumentasi Visual</span>
                    <h2 class="font-serif text-3xl font-semibold text-[#2A211A] mt-1">Galeri Arsip & Stasiun Pabrik</h2>
                </div>
                <span class="text-xs text-[#6E635A]">Menampilkan koleksi arsitektur, stasiun giling, dan emplasemen lori</span>
            </div>

            @if($galleryItems->isEmpty())
                <!-- Edge Case from FEATURES.md §2: Heritage Placeholder when gallery is empty -->
                <div class="border border-[#E3D9C9] bg-[#F3EAD8] p-10 text-center space-y-3">
                    <span class="text-xs uppercase tracking-[0.08em] text-[#6B3F1E] font-semibold block">Arsip Dokumentasi Heritage</span>
                    <h3 class="font-serif text-2xl text-[#2A211A]">Koleksi Foto Arsip Sedang Dalam Kurasi</h3>
                    <p class="text-sm text-[#6E635A] max-w-[54ch] mx-auto">
                        Dokumentasi fotografi stasiun gilingan uap dan lokomotif lori PT Pabrik Gula Candi Baru sedang diperbarui oleh tim kurator.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach($galleryItems as $item)
                        <figure class="border border-[#E3D9C9] bg-[#FFFFFF] p-4 flex flex-col justify-between">
                            <div>
                                <div class="aspect-[16/9] bg-[#F3EAD8] border border-[#E3D9C9] overflow-hidden relative flex items-center justify-center">
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}"
                                             alt="{{ $item->title }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-full h-full" viewBox="0 0 600 340" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ $item->title }}">
                                            <rect width="600" height="340" fill="#F3EAD8"/>
                                            <rect x="24" y="24" width="552" height="292" fill="none" stroke="#A67C52" stroke-width="1.5"/>
                                            <circle cx="300" cy="150" r="46" fill="none" stroke="#6B3F1E" stroke-width="4"/>
                                            <circle cx="300" cy="150" r="10" fill="#C8A24A"/>
                                            <line x1="254" y1="150" x2="346" y2="150" stroke="#6B3F1E" stroke-width="2"/>
                                            <line x1="300" y1="104" x2="300" y2="196" stroke="#6B3F1E" stroke-width="2"/>
                                            <text x="300" y="245" text-anchor="middle" fill="#6B3F1E" font-family="Georgia, serif" font-size="16" font-weight="bold">{{ $item->category }}</text>
                                            <text x="300" y="270" text-anchor="middle" fill="#6E635A" font-family="sans-serif" font-size="12">{{ $item->year_label ?? 'Arsip PG Candi Baru' }}</text>
                                        </svg>
                                    @endif
                                </div>
                                <div class="pt-4 space-y-1.5">
                                    <div class="flex items-center justify-between text-xs text-[#A67C52] font-semibold uppercase tracking-wider">
                                        <span>{{ $item->category }}</span>
                                        <span>{{ $item->year_label }}</span>
                                    </div>
                                    <h3 class="font-serif text-xl font-semibold text-[#2A211A]">{{ $item->title }}</h3>
                                    @if($item->caption)
                                        <p class="text-sm text-[#6E635A] leading-relaxed">{{ $item->caption }}</p>
                                    @endif
                                </div>
                            </div>
                        </figure>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- Contact & Location Details (FR-01) -->
    <section class="py-12 lg:py-16">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-6 space-y-4">
                <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Informasi Kunjungan</span>
                <h2 class="font-serif text-3xl font-semibold text-[#2A211A]">Kontak & Lokasi Pabrik</h2>
                <dl class="divide-y divide-[#E3D9C9] border-t border-b border-[#E3D9C9] text-sm">
                    <div class="py-3.5 grid grid-cols-3 gap-4">
                        <dt class="text-[#6E635A] font-medium">Alamat Lengkap</dt>
                        <dd class="col-span-2 text-[#2A211A]">{{ $profile?->address }}</dd>
                    </div>
                    <div class="py-3.5 grid grid-cols-3 gap-4">
                        <dt class="text-[#6E635A] font-medium">Telepon Resmi</dt>
                        <dd class="col-span-2 text-[#2A211A] tabular-nums">{{ $profile?->phone }}</dd>
                    </div>
                    <div class="py-3.5 grid grid-cols-3 gap-4">
                        <dt class="text-[#6E635A] font-medium">Layanan WhatsApp</dt>
                        <dd class="col-span-2 text-[#2A211A] tabular-nums">+{{ $profile?->whatsapp }}</dd>
                    </div>
                    <div class="py-3.5 grid grid-cols-3 gap-4">
                        <dt class="text-[#6E635A] font-medium">Surel Resmi</dt>
                        <dd class="col-span-2 text-[#2A211A]">{{ $profile?->email }}</dd>
                    </div>
                    <div class="py-3.5 grid grid-cols-3 gap-4">
                        <dt class="text-[#6E635A] font-medium">Jam Operasional</dt>
                        <dd class="col-span-2 text-[#2A211A]">{{ $profile?->operating_hours }}</dd>
                    </div>
                </dl>
                <div class="pt-3">
                    <a href="{{ route('events.index') }}" class="btn-heritage-primary">
                        Lihat Jadwal & Pesan Tiket Tour
                    </a>
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-4 space-y-3">
                    <div class="aspect-[16/10] bg-[#F3EAD8] border border-[#E3D9C9] flex flex-col items-center justify-center p-6 text-center">
                        <span class="text-xs uppercase tracking-[0.08em] text-[#6B3F1E] font-semibold">Titik Kumpul Kunjungan</span>
                        <h3 class="font-serif text-2xl font-bold text-[#2A211A] mt-2">Gerbang Utama PT Pabrik Gula Candi Baru</h3>
                        <p class="text-sm text-[#6E635A] mt-2 max-w-[44ch]">
                            {{ $profile?->address ?? 'Jl. Raya Candi No. 10, Kecamatan Candi, Kabupaten Sidoarjo, Jawa Timur 61271' }}
                        </p>
                        <p class="text-xs text-[#6B3F1E] mt-4 border-t border-[#E3D9C9] pt-3">
                            Peserta diimbau hadir 15 menit sebelum jam sesi dimulai dengan membawa E-Ticket QR untuk pemindaian check-in.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

