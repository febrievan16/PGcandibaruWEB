@extends('layouts.app')

@section('title', $article->title . ' — Artikel Heritage PG Candi Baru')

@section('content')
    <article class="relative overflow-hidden py-12 lg:py-16">
        <div class="relative z-10 max-w-[900px] mx-auto px-4 sm:px-6 space-y-8">
            <!-- Breadcrumb & Admin Quick-Edit Toolbar -->
            <div data-reveal="down" class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E3D9C9] pb-4">
                <div class="flex items-center gap-2 text-xs">
                    <a href="{{ route('profile') }}#artikel-heritage" class="link-heritage-tertiary">Profil Pabrik</a>
                    <span class="text-[#6E635A]">/</span>
                    <a href="{{ route('articles.index') }}" class="link-heritage-tertiary">Artikel Sejarah</a>
                </div>

                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.articles.edit', $article) }}" class="btn-heritage-primary text-xs py-2 px-4">
                                ✎ Edit Artikel Ini (Admin)
                            </a>
                            <a href="{{ route('admin.articles.index') }}" class="btn-heritage-secondary text-xs py-2 px-3">
                                Semua Artikel
                            </a>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- Article Header -->
            <header data-reveal="up" class="space-y-4">
                <div class="flex flex-wrap items-center gap-3 text-xs text-[#6E635A]">
                    <span class="uppercase tracking-[0.08em] font-semibold text-[#6B3F1E]">{{ $article->category }}</span>
                    <span>&bull;</span>
                    <span class="tabular-nums">{{ ($article->published_at ?? $article->created_at)?->translatedFormat('d F Y') }}</span>
                    <span>&bull;</span>
                    <span class="tabular-nums">Waktu Baca {{ $article->reading_time_minutes }} Menit</span>
                    @if(!$article->is_published)
                        <span class="px-2 py-0.5 border border-[#B8860B] text-[#B8860B] bg-[#B8860B]/10 font-semibold">Status: Draft</span>
                    @endif
                </div>

                <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-[#2A211A] leading-tight">
                    {{ $article->title }}
                </h1>

                <p class="text-lg text-[#6E635A] leading-relaxed border-l-2 border-[#6B3F1E] pl-4">
                    {{ $article->excerpt }}
                </p>

                <div class="pt-2 text-xs text-[#6E635A]">
                    Ditulis oleh <strong class="text-[#2A211A]">{{ $article->author_name }}</strong>
                </div>
            </header>

            <!-- Cover Visual with 3D Tilt Stage & Parallax Window -->
            <div class="heritage-3d-stage">
                <figure data-reveal="scale" data-tilt-card data-tilt-max="5" class="heritage-card-interactive border border-[#E3D9C9] bg-[#FFFFFF] p-3">
                    <div class="aspect-[16/9] bg-[#F3EAD8] border border-[#E3D9C9] overflow-hidden flex items-center justify-center">
                        @if($article->cover_image)
                            <img data-parallax-speed="-0.06" src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover scale-105">
                        @else
                            <svg data-parallax-speed="-0.04" class="w-full h-full" viewBox="0 0 800 450" xmlns="http://www.w3.org/2000/svg">
                                <rect width="800" height="450" fill="#F3EAD8"/>
                                <rect x="30" y="30" width="740" height="390" fill="none" stroke="#A67C52" stroke-width="2"/>
                                <text x="400" y="210" text-anchor="middle" fill="#6B3F1E" font-family="Georgia, serif" font-size="24" font-weight="bold">{{ strtoupper($article->category) }}</text>
                                <text x="400" y="245" text-anchor="middle" fill="#6E635A" font-family="sans-serif" font-size="15">DOKUMENTASI &amp; LITERASI SEJARAH PT PABRIK GULA CANDI BARU</text>
                            </svg>
                        @endif
                    </div>
                </figure>
            </div>

            <!-- Editorial Body Prose (68ch max width per DESIGN.md §6) -->
            <div data-reveal-stagger="90" class="max-w-[68ch] mx-auto space-y-5 text-base sm:text-[17px] text-[#2A211A] leading-[1.75]">
                @foreach(preg_split('/\r\n\r\n|\n\n/', $article->content) as $paragraph)
                    @if(trim($paragraph) !== '')
                        <p data-reveal="up">{{ trim($paragraph) }}</p>
                    @endif
                @endforeach
            </div>

            <!-- Footer CTA to Tour Booking -->
            <div data-reveal="up" class="heritage-card-interactive border-t border-b border-[#E3D9C9] bg-[#FFFFFF] p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs uppercase tracking-wider text-[#A67C52] font-semibold block">Kunjungan Langsung</span>
                    <h2 class="font-serif text-xl font-bold text-[#2A211A]">Ingin Menyaksikan Langsung Warisan PG Candi Baru?</h2>
                    <p class="text-xs text-[#6E635A] mt-0.5">Pilih jadwal sesi kunjungan edukasi dan pesan E-Ticket resmi secara daring.</p>
                </div>
                <a href="{{ route('events.index') }}" class="btn-heritage-primary text-sm shrink-0 inline-flex items-center gap-2 group">
                    <span>Lihat Jadwal Tour</span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            <!-- Related Articles -->
            @if($relatedArticles->isNotEmpty())
                <div data-reveal="up" class="pt-4 space-y-4">
                    <h2 class="font-serif text-2xl font-bold text-[#2A211A]">Artikel Sejarah Lainnya</h2>
                    <div data-reveal-stagger="100" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($relatedArticles as $rel)
                            <div data-reveal="up" class="border border-[#E3D9C9] bg-[#FFFFFF] p-4 flex flex-col justify-between space-y-3 hover:border-[#6B3F1E] transition-colors">
                                <div class="space-y-1.5">
                                    <span class="text-[11px] uppercase tracking-wider font-semibold text-[#6B3F1E] block">{{ $rel->category }}</span>
                                    <h3 class="font-serif text-base font-bold text-[#2A211A] leading-snug">
                                        <a href="{{ route('articles.show', $rel->slug) }}" class="hover:text-[#6B3F1E]">{{ $rel->title }}</a>
                                    </h3>
                                </div>
                                <a href="{{ route('articles.show', $rel->slug) }}" class="link-heritage-tertiary text-xs inline-flex items-center gap-1 group">
                                    <span>Baca Artikel</span>
                                    <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </article>
@endsection

