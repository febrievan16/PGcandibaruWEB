@extends('layouts.admin')

@section('title', 'Kelola Profil & Galeri Pabrik — Admin GulaTour')
@section('page_title', 'Manajemen Konten Profil Perusahaan & Galeri (FR-12)')
@section('page_subtitle', 'Perubahan teks sejarah, visi-misi, kontak, dan galeri arsip langsung tampil di halaman publik.')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left 7/12: Company Profile Form -->
        <div class="lg:col-span-7 border border-[#E3D9C9] bg-[#FFFFFF] p-6">
            <h2 class="font-serif text-xl font-bold text-[#2A211A] border-b border-[#E3D9C9] pb-3 mb-5">
                Profil Resmi PT Pabrik Gula Candi Baru
            </h2>

            <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Nama Perusahaan</label>
                        <input type="text" name="company_name" required value="{{ old('company_name', $profile?->company_name) }}" class="input-heritage text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Tahun Berdiri</label>
                        <input type="number" name="established_year" required value="{{ old('established_year', $profile?->established_year ?? 1911) }}" class="input-heritage text-sm tabular-nums">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Tagline Heritage</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $profile?->tagline) }}" class="input-heritage text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Narasi Sejarah</label>
                    <textarea name="history" rows="5" required class="input-heritage text-sm">{{ old('history', $profile?->history) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Visi</label>
                    <textarea name="vision" rows="2" required class="input-heritage text-sm">{{ old('vision', $profile?->vision) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Misi (Satu poin per baris)</label>
                    <textarea name="mission_text" rows="4" required class="input-heritage text-sm">{{ old('mission_text', is_array($profile?->mission) ? implode("\n", $profile->mission) : '') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Alamat Lengkap</label>
                    <input type="text" name="address" required value="{{ old('address', $profile?->address) }}" class="input-heritage text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Telepon</label>
                        <input type="text" name="phone" required value="{{ old('phone', $profile?->phone) }}" class="input-heritage text-sm tabular-nums">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">WhatsApp</label>
                        <input type="text" name="whatsapp" required value="{{ old('whatsapp', $profile?->whatsapp) }}" class="input-heritage text-sm tabular-nums">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Email</label>
                        <input type="email" name="email" required value="{{ old('email', $profile?->email) }}" class="input-heritage text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Jam Operasional</label>
                    <input type="text" name="operating_hours" value="{{ old('operating_hours', $profile?->operating_hours) }}" class="input-heritage text-sm">
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-heritage-primary text-sm">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>

        <!-- Right 5/12: Gallery Items Manager -->
        <div class="lg:col-span-5 space-y-6">
            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4">
                <h2 class="font-serif text-xl font-bold text-[#2A211A]">Tambah Koleksi Galeri Arsip</h2>
                <form action="{{ route('admin.profile.gallery.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Judul Dokumentasi</label>
                        <input type="text" name="title" required class="input-heritage text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Kategori</label>
                            <input type="text" name="category" required value="Arsip Sejarah" class="input-heritage text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Label Tahun / Era</label>
                            <input type="text" name="year_label" placeholder="Era 1920-an" class="input-heritage text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Keterangan Singkat</label>
                        <textarea name="caption" rows="2" class="input-heritage text-sm"></textarea>
                    </div>
                    <button type="submit" class="btn-heritage-secondary text-xs w-full">
                        + Tambahkan ke Galeri
                    </button>
                </form>
            </div>

            <div class="border border-[#E3D9C9] bg-[#FFFFFF] divide-y divide-[#E3D9C9]">
                @forelse($galleryItems as $item)
                    <div class="p-4 flex items-start justify-between gap-3">
                        <div>
                            <span class="text-[11px] uppercase tracking-wider font-semibold text-[#A67C52]">
                                {{ $item->category }} &bull; {{ $item->year_label }}
                            </span>
                            <h3 class="font-serif font-bold text-base text-[#2A211A]">{{ $item->title }}</h3>
                            <p class="text-xs text-[#6E635A] mt-0.5">{{ $item->caption }}</p>
                        </div>
                        <form action="{{ route('admin.profile.gallery.delete', $item) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-[#8B2E1F] hover:underline cursor-pointer">Hapus</button>
                        </form>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-[#6E635A]">Galeri kosong (Halaman publik menampilkan placeholder heritage).</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

