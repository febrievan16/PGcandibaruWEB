@extends('layouts.app')

@section('title', 'Profil, Sejarah & Artikel — ' . ($profile?->company_name ?? 'PT Pabrik Gula Candi Baru'))

@section('content')
    <!-- Editorial Header & Sub-Navigation Menu inside /profil -->
    <section class="relative overflow-hidden border-b border-[#E3D9C9] pt-12 pb-6 lg:pt-16 lg:pb-8">
        <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6 space-y-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div data-reveal="left" class="max-w-[68ch] space-y-4">
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">
                        Profil Resmi &amp; Warisan Sejarah (Sejak <span class="tabular-nums font-semibold">{{ $profile?->established_year ?? 1911 }}</span>)
                    </span>
                    <h1 class="font-serif text-4xl sm:text-5xl font-bold text-[#2A211A]">
                        {{ $profile?->company_name ?? 'PT Pabrik Gula Candi Baru' }}
                    </h1>
                    <p class="text-lg text-[#6E635A] leading-relaxed">
                        {{ $profile?->tagline ?? 'Warisan Industri Gula Nusantara di Jantung Sidoarjo Sejak 1911.' }}
                    </p>
                </div>

                @auth
                    @if(auth()->user()->isAdmin())
                        <div data-reveal="right" class="heritage-card-interactive border border-[#6B3F1E] bg-[#F3EAD8] p-4 space-y-2 shrink-0">
                            <span class="text-[11px] uppercase tracking-wider font-bold text-[#6B3F1E] block">
                                Mode Administrator Aktif
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('admin.profile.edit') }}" class="btn-heritage-secondary text-xs py-1.5 px-3 min-h-[36px]">
                                    Edit Profil & Galeri
                                </a>
                                <a href="{{ route('admin.articles.index') }}" class="btn-heritage-primary text-xs py-1.5 px-3 min-h-[36px]">
                                    Kelola & Edit Artikel
                                </a>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- In-Page Editorial Sub-Menu for /profil -->
            <nav data-reveal="up" data-reveal-delay="120" aria-label="Sub-menu Halaman Profil" class="border-t border-[#E3D9C9] pt-4 flex flex-wrap items-center justify-between gap-4 text-sm">
                <div class="flex flex-wrap items-center gap-6">
                    <a href="#sejarah-visi-misi" class="font-semibold text-[#6B3F1E] hover:underline underline-offset-4">
                        1. Sejarah & Visi-Misi
                    </a>
                    <a href="#artikel-heritage" class="font-semibold text-[#6B3F1E] hover:underline underline-offset-4">
                        2. Artikel & Catatan Sejarah ({{ $articles->count() }})
                    </a>
                    <a href="#galeri-arsip" class="font-semibold text-[#6B3F1E] hover:underline underline-offset-4">
                        3. Galeri Foto Arsip
                    </a>
                    <a href="#kontak-lokasi" class="font-semibold text-[#6B3F1E] hover:underline underline-offset-4">
                        4. Kontak & Lokasi
                    </a>
                </div>
                <a href="{{ route('articles.index') }}" class="link-heritage-tertiary text-xs uppercase tracking-wider">
                    Buka Halaman Indeks Artikel Penuh &rarr;
                </a>
            </nav>
        </div>
    </section>

    <!-- 1. Sejarah & Visi Misi (Editorial Typography without Card Wrappers per DESIGN.md §8) -->
    <section id="sejarah-visi-misi" class="relative overflow-hidden border-b border-[#E3D9C9] py-12 lg:py-16">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div data-reveal="left" class="lg:col-span-7 space-y-5">
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

            <div data-reveal="right" data-reveal-delay="140" class="lg:col-span-5 lg:border-l lg:border-[#E3D9C9] lg:pl-10 space-y-8">
                <div class="pb-8 border-b border-[#E3D9C9]">
                    <h2 class="font-serif text-2xl font-semibold text-[#6B3F1E] mb-3">Visi</h2>
                    <p class="font-serif text-lg text-[#2A211A] leading-relaxed">
                        “{{ $profile?->vision }}”
                    </p>
                </div>

                <div>
                    <h2 class="font-serif text-2xl font-semibold text-[#6B3F1E] mb-4">Misi</h2>
                    <ul data-reveal-stagger="90" class="space-y-4">
                        @foreach($profile?->mission ?? [] as $idx => $item)
                            <li data-reveal="up" class="flex items-start gap-3 pb-3 border-b border-[#E3D9C9] last:border-b-0">
                                <span class="font-serif text-sm font-bold text-[#A67C52] tabular-nums">0{{ $idx + 1 }}</span>
                                <span class="text-sm text-[#2A211A] leading-relaxed">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Artikel & Publikasi Sejarah Pabrik -->
    <section id="artikel-heritage" class="relative overflow-hidden border-b border-[#E3D9C9] py-12 lg:py-16">
        <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6 space-y-8">
            <div data-reveal="up" class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-[#E3D9C9] pb-5">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Publikasi & Edukasi Heritage</span>
                    <h2 class="font-serif text-3xl font-semibold text-[#2A211A] mt-1">Artikel & Catatan Sejarah PG Candi Baru</h2>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.articles.create') }}" class="btn-heritage-primary text-xs py-2 px-4">
                                + Tulis Artikel Baru
                            </a>
                            <a href="{{ route('admin.articles.index') }}" class="btn-heritage-secondary text-xs py-2 px-3">
                                Daftar Edit Artikel
                            </a>
                        @endif
                    @endauth
                    <a href="{{ route('articles.index') }}" class="link-heritage-tertiary text-sm">
                        Lihat Semua Artikel ({{ $articles->count() }}) &rarr;
                    </a>
                </div>
            </div>

            @if($articles->isEmpty())
                <div data-reveal="scale" class="border border-[#E3D9C9] bg-[#FFFFFF] p-10 text-center space-y-3">
                    <h3 class="font-serif text-xl text-[#2A211A]">Belum Ada Artikel yang Dipublikasikan</h3>
                    <p class="text-sm text-[#6E635A]">Artikel sejarah dan edukasi pabrik gula akan tampil di bagian ini.</p>
                </div>
            @else
                <div data-reveal-stagger="120" class="divide-y divide-[#E3D9C9] border-t border-b border-[#E3D9C9]">
                    @foreach($articles as $article)
                        <article data-reveal="up" class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            <!-- Left 4/12: Cover Image or Heritage Archival Plate -->
                            <div class="lg:col-span-4">
                                <a href="{{ route('articles.show', $article->slug) }}" data-tilt-card class="group heritage-card-interactive block border border-[#E3D9C9] bg-[#FFFFFF] p-2 hover:border-[#6B3F1E] transition-colors">
                                    <div class="aspect-[16/10] bg-[#F3EAD8] border border-[#E3D9C9] overflow-hidden flex items-center justify-center">
                                        @if($article->cover_image)
                                            <img src="{{ $article->cover_image }}"
                                                 alt="{{ $article->title }}"
                                                 loading="lazy"
                                                 class="heritage-img-zoom w-full h-full object-cover">
                                        @else
                                            <svg class="heritage-img-zoom w-full h-full" viewBox="0 0 480 300" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ $article->title }}">
                                                <rect width="480" height="300" fill="#F3EAD8"/>
                                                <rect x="18" y="18" width="444" height="264" fill="none" stroke="#A67C52" stroke-width="1.5"/>
                                                <line x1="40" y1="75" x2="440" y2="75" stroke="#E3D9C9" stroke-width="1"/>
                                                <line x1="40" y1="225" x2="440" y2="225" stroke="#E3D9C9" stroke-width="1"/>
                                                <text x="240" y="135" text-anchor="middle" fill="#6B3F1E" font-family="Georgia, serif" font-size="15" font-weight="bold">{{ strtoupper($article->category) }}</text>
                                                <text x="240" y="165" text-anchor="middle" fill="#2A211A" font-family="Georgia, serif" font-size="13">ARSIP LITERASI PG CANDI BARU</text>
                                                <text x="240" y="205" text-anchor="middle" fill="#6E635A" font-family="sans-serif" font-size="11">Waktu Baca {{ $article->reading_time_minutes }} Menit</text>
                                            </svg>
                                        @endif
                                    </div>
                                </a>
                            </div>

                            <!-- Right 8/12: Editorial Metadata, Title, Excerpt & Admin Quick Edit -->
                            <div class="lg:col-span-8 space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                    <div class="flex flex-wrap items-center gap-2.5 text-[#6E635A]">
                                        <span class="uppercase tracking-[0.06em] font-semibold text-[#6B3F1E]">{{ $article->category }}</span>
                                        <span>&bull;</span>
                                        <span class="tabular-nums">{{ ($article->published_at ?? $article->created_at)?->translatedFormat('d F Y') }}</span>
                                        <span>&bull;</span>
                                        <span class="tabular-nums">{{ $article->reading_time_minutes }} menit baca</span>
                                        @if(!$article->is_published)
                                            <span class="px-2 py-0.5 border border-[#B8860B] text-[#B8860B] bg-[#B8860B]/10 font-semibold">Draft (Hanya Admin)</span>
                                        @endif
                                    </div>

                                    @auth
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.articles.edit', $article) }}"
                                               class="px-3 py-1 text-xs font-semibold border border-[#6B3F1E] bg-[#F3EAD8] text-[#6B3F1E] hover:bg-[#6B3F1E] hover:text-[#FAF6EE] transition-colors">
                                                ✎ Edit Artikel Ini
                                            </a>
                                        @endif
                                    @endauth
                                </div>

                                <h3 class="font-serif text-2xl font-bold text-[#2A211A] leading-snug">
                                    <a href="{{ route('articles.show', $article->slug) }}" class="hover:text-[#6B3F1E] transition-colors">
                                        {{ $article->title }}
                                    </a>
                                </h3>

                                <p class="text-sm text-[#6E635A] leading-relaxed max-w-[68ch]">
                                    {{ $article->excerpt }}
                                </p>

                                <div class="pt-1 flex items-center justify-between text-xs">
                                    <span class="text-[#6E635A]">Penulis: <strong class="text-[#2A211A]">{{ $article->author_name }}</strong></span>
                                    <a href="{{ route('articles.show', $article->slug) }}" class="link-heritage-tertiary text-sm">
                                        Baca Selengkapnya &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- 3. Archival Gallery with Lazy Loading & Heritage Placeholder Edge Case (FEATURES.md §2) -->
    <section id="galeri-arsip" class="relative overflow-hidden border-b border-[#E3D9C9] py-12 lg:py-16">
        <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6 space-y-8">
            <div data-reveal="up" class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-[#E3D9C9] pb-5">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Dokumentasi Visual</span>
                    <h2 class="font-serif text-3xl font-semibold text-[#2A211A] mt-1">Galeri Arsip & Stasiun Pabrik</h2>
                </div>
                <span class="text-xs text-[#6E635A]">Menampilkan koleksi arsitektur, stasiun giling, dan emplasemen lori</span>
            </div>

            @if($galleryItems->isEmpty())
                <!-- Edge Case from FEATURES.md §2: Heritage Placeholder when gallery is empty -->
                <div data-reveal="scale" class="border border-[#E3D9C9] bg-[#F3EAD8] p-10 text-center space-y-3">
                    <span class="text-xs uppercase tracking-[0.08em] text-[#6B3F1E] font-semibold block">Arsip Dokumentasi Heritage</span>
                    <h3 class="font-serif text-2xl text-[#2A211A]">Koleksi Foto Arsip Sedang Dalam Kurasi</h3>
                    <p class="text-sm text-[#6E635A] max-w-[54ch] mx-auto">
                        Dokumentasi fotografi stasiun gilingan uap dan lokomotif lori PT Pabrik Gula Candi Baru sedang diperbarui oleh tim kurator.
                    </p>
                </div>
            @else
                <div data-reveal-stagger="115" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach($galleryItems as $item)
                        @php
                            $slides = $item->gallerySlides();
                        @endphp
                        <figure tabindex="0"
                                role="button"
                                aria-label="Buka pop-up koleksi foto {{ $item->title }} ({{ count($slides) }} foto)"
                                data-reveal="up"
                                data-tilt-card
                                data-tilt-max="2.2"
                                data-gallery-trigger
                                data-gallery-title="{{ $item->title }}"
                                data-gallery-category="{{ $item->category }}"
                                data-gallery-year="{{ $item->year_label ?? 'Arsip PG Candi Baru' }}"
                                data-gallery-caption="{{ $item->caption }}"
                                data-gallery-slides='@json($slides)'
                                class="group heritage-card-interactive border border-[#E3D9C9] bg-[#FFFFFF] p-4 flex flex-col justify-between cursor-pointer hover:border-[#6B3F1E] transition-colors">
                            <div>
                                <div class="aspect-[16/9] bg-[#F3EAD8] border border-[#E3D9C9] overflow-hidden relative flex items-center justify-center">
                                    <img src="{{ $slides[0]['url'] }}"
                                         alt="{{ $item->title }}"
                                         loading="lazy"
                                         class="heritage-img-zoom w-full h-full object-cover">

                                    <!-- Multi-Photo Count Indicator Badge -->
                                    <div class="absolute bottom-3 right-3 bg-[#2A211A]/90 text-[#FAF6EE] px-3 py-1 text-xs font-semibold tracking-wider flex items-center gap-1.5 border border-[#C8A24A]">
                                        <span>Lihat {{ count($slides) }} Foto</span>
                                        <span>&nearr;</span>
                                    </div>
                                </div>

                                <!-- Mini Thumbnail Strip Preview -->
                                @if(count($slides) > 1)
                                    <div class="grid grid-cols-3 gap-2 pt-2">
                                        @foreach(array_slice($slides, 0, 3) as $thumbIndex => $thumbSlide)
                                            <div class="h-12 border border-[#E3D9C9] bg-[#FAF6EE] overflow-hidden">
                                                <img src="{{ $thumbSlide['url'] }}"
                                                     alt="{{ $thumbSlide['caption'] }}"
                                                     loading="lazy"
                                                     class="w-full h-full object-cover opacity-85 group-hover:opacity-100 transition-opacity">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="pt-4 space-y-1.5">
                                    <div class="flex items-center justify-between text-xs text-[#A67C52] font-semibold uppercase tracking-wider">
                                        <span>{{ $item->category }}</span>
                                        <span>{{ $item->year_label }}</span>
                                    </div>
                                    <h3 class="font-serif text-xl font-semibold text-[#2A211A] group-hover:text-[#6B3F1E] transition-colors">
                                        {{ $item->title }}
                                    </h3>
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

    <!-- Multi-Photo Gallery Lightbox Popup Modal -->
    <div id="gallery-lightbox-modal"
         role="dialog"
         aria-modal="true"
         aria-labelledby="lightbox-title"
         class="hidden fixed inset-0 z-50 bg-[#2A211A]/85 flex items-center justify-center p-4 sm:p-6">
        <div class="bg-[#FAF6EE] border-2 border-[#6B3F1E] max-w-[960px] w-full max-h-[92vh] flex flex-col overflow-hidden">
            <!-- Modal Top Bar -->
            <div class="px-5 py-3.5 border-b border-[#E3D9C9] bg-[#FFFFFF] flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs uppercase tracking-wider font-semibold text-[#A67C52]">
                        <span id="lightbox-category">Arsip Sejarah</span>
                        <span>&bull;</span>
                        <span id="lightbox-year">1911</span>
                        <span>&bull;</span>
                        <span id="lightbox-counter" class="text-[#6B3F1E] tabular-nums">Foto 1 dari 3</span>
                    </div>
                    <h3 id="lightbox-title" class="font-serif text-xl sm:text-2xl font-bold text-[#2A211A] mt-0.5">
                        Judul Dokumentasi
                    </h3>
                </div>
                <button type="button"
                        id="lightbox-close-btn"
                        aria-label="Tutup pop-up galeri"
                        class="btn-heritage-secondary text-xs py-1.5 px-3 min-h-[38px] shrink-0">
                    Tutup &times;
                </button>
            </div>

            <!-- Main Active Slide Viewer + Prev/Next Controls -->
            <div class="p-4 sm:p-6 flex-1 overflow-y-auto space-y-4">
                <div class="relative bg-[#FFFFFF] border border-[#E3D9C9] p-2">
                    <div class="aspect-[16/9] bg-[#F3EAD8] overflow-hidden flex items-center justify-center">
                        <img id="lightbox-main-image"
                             src=""
                             alt=""
                             class="w-full h-full object-contain max-h-[52vh]">
                    </div>

                    <!-- Prev / Next Buttons -->
                    <button type="button"
                            id="lightbox-prev-btn"
                            aria-label="Foto sebelumnya"
                            class="absolute left-4 top-1/2 -translate-y-1/2 bg-[#FAF6EE]/95 border border-[#6B3F1E] text-[#2A211A] hover:bg-[#C8A24A] px-3.5 py-2 text-sm font-bold cursor-pointer min-h-[44px]">
                        &larr; Sebelumnya
                    </button>
                    <button type="button"
                            id="lightbox-next-btn"
                            aria-label="Foto berikutnya"
                            class="absolute right-4 top-1/2 -translate-y-1/2 bg-[#FAF6EE]/95 border border-[#6B3F1E] text-[#2A211A] hover:bg-[#C8A24A] px-3.5 py-2 text-sm font-bold cursor-pointer min-h-[44px]">
                        Berikutnya &rarr;
                    </button>
                </div>

                <!-- Active Slide Caption & Description -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#E3D9C9] pb-3">
                    <p id="lightbox-slide-caption" class="text-sm font-semibold text-[#6B3F1E]"></p>
                    <p id="lightbox-description" class="text-xs text-[#6E635A]"></p>
                </div>

                <!-- Interactive Thumbnail Strip Inside Popup -->
                <div>
                    <span class="text-xs uppercase tracking-wider text-[#6E635A] font-semibold block mb-2">
                        Daftar Foto Dalam Galeri Ini (Klik untuk Memilih):
                    </span>
                    <div id="lightbox-thumbnails" class="grid grid-cols-3 sm:grid-cols-5 gap-3"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('gallery-lightbox-modal');
                const closeBtn = document.getElementById('lightbox-close-btn');
                const prevBtn = document.getElementById('lightbox-prev-btn');
                const nextBtn = document.getElementById('lightbox-next-btn');
                const titleEl = document.getElementById('lightbox-title');
                const categoryEl = document.getElementById('lightbox-category');
                const yearEl = document.getElementById('lightbox-year');
                const counterEl = document.getElementById('lightbox-counter');
                const mainImg = document.getElementById('lightbox-main-image');
                const slideCaptionEl = document.getElementById('lightbox-slide-caption');
                const descEl = document.getElementById('lightbox-description');
                const thumbsContainer = document.getElementById('lightbox-thumbnails');

                let activeSlides = [];
                let currentIndex = 0;

                function renderActiveSlide(index) {
                    if (!activeSlides.length) return;
                    currentIndex = (index + activeSlides.length) % activeSlides.length;
                    const slide = activeSlides[currentIndex];

                    mainImg.src = slide.url;
                    mainImg.alt = slide.caption || '';
                    slideCaptionEl.textContent = slide.caption || '';
                    counterEl.textContent = 'Foto ' + (currentIndex + 1) + ' dari ' + activeSlides.length;

                    const showNav = activeSlides.length > 1;
                    prevBtn.style.display = showNav ? 'inline-flex' : 'none';
                    nextBtn.style.display = showNav ? 'inline-flex' : 'none';

                    // Highlight active thumbnail
                    const thumbButtons = thumbsContainer.querySelectorAll('[data-thumb-index]');
                    thumbButtons.forEach(function (btn, idx) {
                        if (idx === currentIndex) {
                            btn.classList.add('border-2', 'border-[#6B3F1E]', 'bg-[#F3EAD8]');
                            btn.classList.remove('border-[#E3D9C9]', 'opacity-65');
                        } else {
                            btn.classList.remove('border-2', 'border-[#6B3F1E]', 'bg-[#F3EAD8]');
                            btn.classList.add('border-[#E3D9C9]', 'opacity-65');
                        }
                    });
                }

                function openGalleryModal(card) {
                    try {
                        activeSlides = JSON.parse(card.getAttribute('data-gallery-slides') || '[]');
                    } catch (e) {
                        activeSlides = [];
                    }
                    if (!activeSlides.length) return;

                    titleEl.textContent = card.getAttribute('data-gallery-title') || '';
                    categoryEl.textContent = card.getAttribute('data-gallery-category') || '';
                    yearEl.textContent = card.getAttribute('data-gallery-year') || '';
                    descEl.textContent = card.getAttribute('data-gallery-caption') || '';

                    thumbsContainer.innerHTML = '';
                    activeSlides.forEach(function (slide, idx) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.setAttribute('data-thumb-index', idx);
                        btn.className = 'p-1 border cursor-pointer transition-all text-left';
                        btn.innerHTML = '<div class="aspect-[16/10] overflow-hidden bg-[#FAF6EE]"><img src="' + slide.url + '" alt="Slide ' + (idx + 1) + '" class="w-full h-full object-cover"></div>' +
                            '<span class="block text-[11px] font-semibold text-[#2A211A] mt-1 tabular-nums">Foto #' + (idx + 1) + '</span>';
                        btn.addEventListener('click', function () {
                            renderActiveSlide(idx);
                        });
                        thumbsContainer.appendChild(btn);
                    });

                    renderActiveSlide(0);
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }

                function closeGalleryModal() {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }

                document.querySelectorAll('[data-gallery-trigger]').forEach(function (card) {
                    card.addEventListener('click', function () {
                        openGalleryModal(card);
                    });
                    card.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            openGalleryModal(card);
                        }
                    });
                });

                if (closeBtn) {
                    closeBtn.addEventListener('click', closeGalleryModal);
                }
                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        renderActiveSlide(currentIndex - 1);
                    });
                }
                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        renderActiveSlide(currentIndex + 1);
                    });
                }
                if (modal) {
                    modal.addEventListener('click', function (e) {
                        if (e.target === modal) {
                            closeGalleryModal();
                        }
                    });
                }
                document.addEventListener('keydown', function (e) {
                    if (!modal || modal.classList.contains('hidden')) return;
                    if (e.key === 'Escape') closeGalleryModal();
                    if (e.key === 'ArrowLeft') renderActiveSlide(currentIndex - 1);
                    if (e.key === 'ArrowRight') renderActiveSlide(currentIndex + 1);
                });
            });
        </script>
    @endpush

    <!-- 4. Contact & Location Details (FR-01) -->
    <section id="kontak-lokasi" class="relative overflow-hidden py-12 lg:py-16">
        <div aria-hidden="true"
             data-parallax-speed="0.10"
             class="heritage-watermark absolute top-8 right-6 text-[64px] sm:text-[118px]">
            SIDOARJO 61271
        </div>

        <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div data-reveal="left" class="lg:col-span-6 space-y-4">
                <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Informasi Kunjungan</span>
                <h2 class="font-serif text-3xl font-semibold text-[#2A211A]">Kontak & Lokasi Pabrik</h2>
                <dl data-reveal-stagger="80" class="divide-y divide-[#E3D9C9] border-t border-b border-[#E3D9C9] text-sm">
                    <div data-reveal="up" class="py-3.5 grid grid-cols-3 gap-4">
                        <dt class="text-[#6E635A] font-medium">Alamat Lengkap</dt>
                        <dd class="col-span-2 text-[#2A211A]">{{ $profile?->address }}</dd>
                    </div>
                    <div data-reveal="up" class="py-3.5 grid grid-cols-3 gap-4">
                        <dt class="text-[#6E635A] font-medium">Telepon Resmi</dt>
                        <dd class="col-span-2 text-[#2A211A] tabular-nums">{{ $profile?->phone }}</dd>
                    </div>
                    <div data-reveal="up" class="py-3.5 grid grid-cols-3 gap-4">
                        <dt class="text-[#6E635A] font-medium">Layanan WhatsApp</dt>
                        <dd class="col-span-2 text-[#2A211A] tabular-nums">+{{ $profile?->whatsapp }}</dd>
                    </div>
                    <div data-reveal="up" class="py-3.5 grid grid-cols-3 gap-4">
                        <dt class="text-[#6E635A] font-medium">Surel Resmi</dt>
                        <dd class="col-span-2 text-[#2A211A]">{{ $profile?->email }}</dd>
                    </div>
                    <div data-reveal="up" class="py-3.5 grid grid-cols-3 gap-4">
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

            <div data-reveal="right" data-reveal-delay="140" class="lg:col-span-6">
                <div data-tilt-card class="heritage-card-interactive border border-[#E3D9C9] bg-[#FFFFFF] p-4 space-y-3">
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
