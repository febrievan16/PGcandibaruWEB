@extends('layouts.app')

@section('title', 'Artikel & Catatan Sejarah — PT Pabrik Gula Candi Baru')

@section('content')
    <section class="border-b border-[#E3D9C9] py-12">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 space-y-4">
            <div>
                <a href="{{ route('profile') }}" class="link-heritage-tertiary text-xs uppercase tracking-wider">
                    &larr; Kembali ke Halaman Profil & Sejarah Pabrik
                </a>
            </div>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Publikasi Heritage PT PG Candi Baru</span>
                    <h1 class="font-serif text-4xl font-bold text-[#2A211A] mt-1">Artikel & Catatan Sejarah Pabrik</h1>
                </div>

                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.articles.create') }}" class="btn-heritage-primary text-xs py-2 px-4">
                                + Tulis Artikel Baru
                            </a>
                            <a href="{{ route('admin.articles.index') }}" class="btn-heritage-secondary text-xs py-2 px-3">
                                Panel Kelola Artikel
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </section>

    <section class="py-12">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
            @if($articles->isEmpty())
                <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-12 text-center space-y-3">
                    <h2 class="font-serif text-2xl text-[#2A211A]">Belum ada artikel yang diterbitkan.</h2>
                    <p class="text-sm text-[#6E635A]">Silakan kembali ke halaman profil perusahaan.</p>
                </div>
            @else
                <div class="divide-y divide-[#E3D9C9] border-t border-b border-[#E3D9C9]">
                    @foreach($articles as $article)
                        <article class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            <div class="lg:col-span-4">
                                <a href="{{ route('articles.show', $article->slug) }}" class="block border border-[#E3D9C9] bg-[#FFFFFF] p-2 hover:border-[#6B3F1E]">
                                    <div class="aspect-[16/10] bg-[#F3EAD8] border border-[#E3D9C9] overflow-hidden flex items-center justify-center">
                                        @if($article->cover_image)
                                            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" loading="lazy" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-full h-full" viewBox="0 0 480 300" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="480" height="300" fill="#F3EAD8"/>
                                                <rect x="18" y="18" width="444" height="264" fill="none" stroke="#A67C52" stroke-width="1.5"/>
                                                <text x="240" y="145" text-anchor="middle" fill="#6B3F1E" font-family="Georgia, serif" font-size="16" font-weight="bold">{{ strtoupper($article->category) }}</text>
                                                <text x="240" y="175" text-anchor="middle" fill="#6E635A" font-family="sans-serif" font-size="12">PT Pabrik Gula Candi Baru</text>
                                            </svg>
                                        @endif
                                    </div>
                                </a>
                            </div>

                            <div class="lg:col-span-8 space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-2 text-[#6E635A]">
                                        <span class="uppercase tracking-wider font-semibold text-[#6B3F1E]">{{ $article->category }}</span>
                                        <span>&bull;</span>
                                        <span class="tabular-nums">{{ ($article->published_at ?? $article->created_at)?->translatedFormat('d F Y') }}</span>
                                        <span>&bull;</span>
                                        <span class="tabular-nums">{{ $article->reading_time_minutes }} menit baca</span>
                                        @if(!$article->is_published)
                                            <span class="px-2 py-0.5 border border-[#B8860B] text-[#B8860B] bg-[#B8860B]/10 font-semibold">Draft</span>
                                        @endif
                                    </div>

                                    @auth
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.articles.edit', $article) }}"
                                               class="px-3 py-1 text-xs font-semibold border border-[#6B3F1E] bg-[#F3EAD8] text-[#6B3F1E] hover:bg-[#6B3F1E] hover:text-[#FAF6EE]">
                                                ✎ Edit Artikel
                                            </a>
                                        @endif
                                    @endauth
                                </div>

                                <h2 class="font-serif text-2xl font-bold text-[#2A211A]">
                                    <a href="{{ route('articles.show', $article->slug) }}" class="hover:text-[#6B3F1E]">
                                        {{ $article->title }}
                                    </a>
                                </h2>

                                <p class="text-sm text-[#6E635A] leading-relaxed max-w-[68ch]">
                                    {{ $article->excerpt }}
                                </p>

                                <div class="pt-1 flex items-center justify-between text-xs">
                                    <span class="text-[#6E635A]">Oleh: <strong class="text-[#2A211A]">{{ $article->author_name }}</strong></span>
                                    <a href="{{ route('articles.show', $article->slug) }}" class="link-heritage-tertiary text-sm">
                                        Baca Artikel Penuh &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection

