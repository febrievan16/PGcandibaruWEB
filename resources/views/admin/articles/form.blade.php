@extends('layouts.admin')

@php
    $isEdit = ($article !== null && $article->exists);
@endphp

@section('title', ($isEdit ? 'Edit Artikel: ' . $article->title : 'Tulis Artikel Baru') . ' — Admin GulaTour')
@section('page_title', $isEdit ? 'Edit Artikel Heritage' : 'Tulis Artikel Heritage Baru')
@section('page_subtitle', 'Artikel yang dipublikasikan akan langsung tampil pada halaman /profil dan /profil/artikel.')

@section('header_actions')
    @if($isEdit)
        <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="btn-heritage-secondary text-xs py-2 px-3">
            Lihat Artikel di Publik &nearr;
        </a>
    @endif
    <a href="{{ route('admin.articles.index') }}" class="btn-heritage-secondary text-xs py-2 px-3">
        &larr; Daftar Artikel
    </a>
@endsection

@section('content')
    <form action="{{ $isEdit ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-6 max-w-[960px]">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-5">
            <div class="sm:col-span-8">
                <label for="title" class="block text-sm font-semibold text-[#2A211A] mb-1">
                    Judul Artikel <span class="text-[#8B2E1F]">*</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       required
                       value="{{ old('title', $article?->title) }}"
                       placeholder="Contoh: Jejak Tahun 1911: Berdirinya PT Pabrik Gula Candi Baru"
                       class="input-heritage">
            </div>

            <div class="sm:col-span-4">
                <label for="category" class="block text-sm font-semibold text-[#2A211A] mb-1">
                    Kategori Topik <span class="text-[#8B2E1F]">*</span>
                </label>
                <input type="text"
                       id="category"
                       name="category"
                       required
                       value="{{ old('category', $article?->category ?? 'Arsip & Sejarah') }}"
                       placeholder="Arsip & Sejarah / Teknologi Uap"
                       class="input-heritage">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-5">
            <div class="sm:col-span-5">
                <label for="author_name" class="block text-sm font-semibold text-[#2A211A] mb-1">
                    Nama Penulis / Tim Kurator <span class="text-[#8B2E1F]">*</span>
                </label>
                <input type="text"
                       id="author_name"
                       name="author_name"
                       required
                       value="{{ old('author_name', $article?->author_name ?? 'Tim Kurator Sejarah PG Candi Baru') }}"
                       class="input-heritage">
            </div>

            <div class="sm:col-span-4">
                <label for="slug" class="block text-sm font-semibold text-[#2A211A] mb-1">
                    Slug URL (Opsional)
                </label>
                <input type="text"
                       id="slug"
                       name="slug"
                       value="{{ old('slug', $article?->slug) }}"
                       placeholder="Otomatis dari judul"
                       class="input-heritage">
            </div>

            <div class="sm:col-span-3">
                <label for="reading_time_minutes" class="block text-sm font-semibold text-[#2A211A] mb-1">
                    Estimasi Baca (Menit)
                </label>
                <input type="number"
                       id="reading_time_minutes"
                       name="reading_time_minutes"
                       min="1"
                       max="60"
                       value="{{ old('reading_time_minutes', $article?->reading_time_minutes ?? 4) }}"
                       class="input-heritage tabular-nums">
            </div>
        </div>

        <!-- Cover Image Upload (JPG/PNG) -->
        <div class="border border-[#E3D9C9] bg-[#FAF6EE] p-4 space-y-3">
            <label for="cover_file" class="block text-sm font-semibold text-[#2A211A]">
                Upload Foto Sampul Artikel (JPG, JPEG, PNG — Maks 5MB)
            </label>
            <input type="file"
                   id="cover_file"
                   name="cover_file"
                   accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                   class="input-heritage bg-[#FFFFFF] text-xs file:mr-3 file:py-1.5 file:px-3 file:border file:border-[#6B3F1E] file:bg-[#F3EAD8] file:text-[#6B3F1E] file:font-semibold file:rounded-[2px] cursor-pointer">
            <span class="text-xs text-[#6E635A] block">
                Apabila tidak diunggah, sistem menampilkan pelat ilustrasi arsip heritage secara otomatis.
            </span>

            @if($isEdit && $article->cover_image)
                <div class="pt-2">
                    <span class="text-xs text-[#6E635A] block mb-1">Sampul Saat Ini:</span>
                    <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="h-40 object-cover border border-[#E3D9C9]">
                </div>
            @endif
        </div>

        <div>
            <label for="excerpt" class="block text-sm font-semibold text-[#2A211A] mb-1">
                Ringkasan Singkat (Excerpt) <span class="text-[#8B2E1F]">*</span>
            </label>
            <textarea id="excerpt"
                      name="excerpt"
                      rows="3"
                      required
                      placeholder="Ringkasan 1-2 kalimat yang akan tampil pada kartu daftar artikel di halaman /profil..."
                      class="input-heritage">{{ old('excerpt', $article?->excerpt) }}</textarea>
        </div>

        <div>
            <label for="content" class="block text-sm font-semibold text-[#2A211A] mb-1">
                Isi Lengkap Artikel <span class="text-[#8B2E1F]">*</span>
            </label>
            <textarea id="content"
                      name="content"
                      rows="12"
                      required
                      placeholder="Tuliskan isi artikel lengkap. Pisahkan antar paragraf dengan baris kosong..."
                      class="input-heritage leading-relaxed">{{ old('content', $article?->content) }}</textarea>
            <span class="text-xs text-[#6E635A] mt-1 block">
                Gunakan satu baris kosong (Enter 2x) untuk memisahkan antar paragraf editorial.
            </span>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 pt-3 border-t border-[#E3D9C9]">
            <label class="inline-flex items-center gap-2.5 cursor-pointer text-sm font-semibold text-[#2A211A]">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox"
                       name="is_published"
                       value="1"
                       @checked(old('is_published', $article?->is_published ?? true))
                       class="w-4 h-4 accent-[#6B3F1E]">
                <span>Tampilkan / Publikasikan langsung di halaman Profil Publik</span>
            </label>

            <button type="submit" class="btn-heritage-primary px-8">
                {{ $isEdit ? 'Simpan Perubahan Artikel' : 'Terbitkan Artikel' }}
            </button>
        </div>
    </form>
@endsection

