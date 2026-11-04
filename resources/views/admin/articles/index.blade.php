@extends('layouts.admin')

@section('title', 'Kelola Artikel Sejarah — Admin GulaTour')
@section('page_title', 'Manajemen Artikel & Catatan Sejarah Pabrik')
@section('page_subtitle', 'Kelola artikel edukasi dan sejarah yang tampil pada menu Profil Perusahaan (/profil).')

@section('header_actions')
    <a href="{{ route('profile') }}#artikel-heritage" class="btn-heritage-secondary text-xs py-2 px-3">
        Lihat di Halaman /profil
    </a>
    <a href="{{ route('admin.articles.create') }}" class="btn-heritage-primary text-xs py-2 px-4">
        + Tulis Artikel Baru
    </a>
@endsection

@section('content')
    <div class="border border-[#E3D9C9] bg-[#FFFFFF] overflow-x-auto">
        <table class="w-full text-sm text-left border-collapse">
            <thead>
                <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                    <th class="py-3.5 px-4 font-semibold">Sampul</th>
                    <th class="py-3.5 px-4 font-semibold">Judul Artikel & Slug</th>
                    <th class="py-3.5 px-4 font-semibold">Kategori</th>
                    <th class="py-3.5 px-4 font-semibold">Penulis</th>
                    <th class="py-3.5 px-4 font-semibold">Status</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E3D9C9]">
                @forelse($articles as $article)
                    <tr class="hover:bg-[#FAF6EE]/60">
                        <td class="py-3.5 px-4 w-20">
                            @if($article->cover_image)
                                <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-16 h-12 object-cover border border-[#E3D9C9]">
                            @else
                                <div class="w-16 h-12 border border-[#E3D9C9] bg-[#F3EAD8] flex items-center justify-center text-[10px] font-semibold text-[#6B3F1E]">
                                    Arsip SVG
                                </div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <strong class="font-serif text-base text-[#2A211A] block">{{ $article->title }}</strong>
                            <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="text-xs text-[#6B3F1E] hover:underline">
                                /profil/artikel/{{ $article->slug }} &nearr;
                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-semibold text-[#6B3F1E] uppercase tracking-wider">
                            {{ $article->category }}
                        </td>
                        <td class="py-3.5 px-4 text-xs text-[#6E635A]">
                            {{ $article->author_name }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($article->is_published)
                                <span class="px-2.5 py-1 text-xs font-semibold border border-[#4A6B3A] text-[#4A6B3A] bg-[#4A6B3A]/10">
                                    Dipublikasikan
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold border border-[#B8860B] text-[#B8860B] bg-[#B8860B]/10">
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-2">
                            <a href="{{ route('admin.articles.edit', $article) }}" class="btn-heritage-secondary text-xs py-1.5 px-3 min-h-[34px]">
                                Edit Artikel
                            </a>
                            <form action="{{ route('admin.articles.delete', $article) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Hapus artikel ini?')"
                                        class="text-xs text-[#8B2E1F] hover:underline px-2 cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-[#6E635A]">Belum ada artikel sejarah. Klik "+ Tulis Artikel Baru" untuk menambahkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

