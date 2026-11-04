@extends('layouts.admin')

@php
    $isEdit = ($event !== null && $event->exists);
@endphp

@section('title', ($isEdit ? 'Edit Event: ' . $event->title : 'Buat Event Baru') . ' — Admin GulaTour')
@section('page_title', $isEdit ? 'Kelola Event: ' . $event->title : 'Buat Event Tour Baru')
@section('page_subtitle', 'Form Event 3-Tab (DESIGN.md §6): Info Event, Jadwal Sesi, serta Harga & Kuota.')

@section('header_actions')
    <a href="{{ route('admin.events.index') }}" class="btn-heritage-secondary text-xs py-2 px-3">
        &larr; Kembali ke Daftar Event
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- 3-Tab Header Navigation per DESIGN.md §6 -->
        <div role="tablist" aria-label="Editor Event 3-Tab" class="flex border-b border-[#E3D9C9] bg-[#FFFFFF]">
            <button type="button"
                    data-tab-target="info"
                    class="tab-trigger px-6 py-3.5 text-sm font-semibold border-b-2 cursor-pointer {{ $activeTab === 'info' ? 'border-[#6B3F1E] text-[#6B3F1E] bg-[#F3EAD8]/50' : 'border-transparent text-[#6E635A]' }}">
                1. Informasi Event
            </button>
            <button type="button"
                    data-tab-target="schedules"
                    @disabled(!$isEdit)
                    class="tab-trigger px-6 py-3.5 text-sm font-semibold border-b-2 cursor-pointer {{ $activeTab === 'schedules' ? 'border-[#6B3F1E] text-[#6B3F1E] bg-[#F3EAD8]/50' : 'border-transparent text-[#6E635A]' }} {{ !$isEdit ? 'opacity-40 cursor-not-allowed' : '' }}">
                2. Jadwal & Sesi ({{ $isEdit ? $event->schedules->count() : 0 }})
            </button>
            <button type="button"
                    data-tab-target="pricing"
                    class="tab-trigger px-6 py-3.5 text-sm font-semibold border-b-2 cursor-pointer {{ $activeTab === 'pricing' ? 'border-[#6B3F1E] text-[#6B3F1E] bg-[#F3EAD8]/50' : 'border-transparent text-[#6E635A]' }}">
                3. Harga Tiket Tetap & Status Publikasi
            </button>
        </div>

        <!-- Form for Tab 1 (Info) & Tab 3 (Pricing/Status) -->
        <form action="{{ $isEdit ? route('admin.events.update', $event) : route('admin.events.store') }}"
              method="POST"
              class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <input type="hidden" id="return_tab" name="return_tab" value="{{ $activeTab }}">

            <!-- TAB 1: Informasi Event -->
            <div data-tab-panel="info" class="{{ $activeTab === 'schedules' ? 'hidden' : '' }} space-y-5">
                <h2 class="font-serif text-xl font-bold text-[#2A211A] border-b border-[#E3D9C9] pb-2">
                    Tab 1: Informasi Dasar & Deskripsi Heritage
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="title" class="block text-sm font-semibold text-[#2A211A] mb-1">Judul Event Tour <span class="text-[#8B2E1F]">*</span></label>
                        <input type="text" id="title" name="title" required value="{{ old('title', $event?->title) }}" class="input-heritage">
                    </div>
                    <div>
                        <label for="slug" class="block text-sm font-semibold text-[#2A211A] mb-1">Slug URL (Opsional)</label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $event?->slug) }}" placeholder="Otomatis dari judul" class="input-heritage">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-[#2A211A] mb-1">Deskripsi Lengkap Event <span class="text-[#8B2E1F]">*</span></label>
                    <textarea id="description" name="description" rows="4" required class="input-heritage">{{ old('description', $event?->description) }}</textarea>
                </div>

                <div>
                    <label for="highlights_text" class="block text-sm font-semibold text-[#2A211A] mb-1">Poin Sorotan Kegiatan (Satu per baris)</label>
                    <textarea id="highlights_text" name="highlights_text" rows="4" class="input-heritage">{{ old('highlights_text', is_array($event?->highlights) ? implode("\n", $event->highlights) : '') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="duration_minutes" class="block text-sm font-semibold text-[#2A211A] mb-1">Durasi Kunjungan (Menit)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $event?->duration_minutes ?? 120) }}" required class="input-heritage tabular-nums">
                    </div>
                    <div>
                        <label for="meeting_point" class="block text-sm font-semibold text-[#2A211A] mb-1">Titik Kumpul (Meeting Point)</label>
                        <input type="text" id="meeting_point" name="meeting_point" value="{{ old('meeting_point', $event?->meeting_point ?? 'Gerbang Utama PG Candi Baru') }}" required class="input-heritage">
                    </div>
                </div>
            </div>

            <!-- TAB 3: Harga Tiket Tetap & Status Aktivasi (FR-04, DEC-001) -->
            <div data-tab-panel="pricing" class="{{ $activeTab === 'schedules' ? 'hidden' : '' }} pt-4 border-t border-[#E3D9C9] space-y-5">
                <h2 class="font-serif text-xl font-bold text-[#2A211A] border-b border-[#E3D9C9] pb-2">
                    Tab 3: Harga Tiket Tetap (DEC-001) & Status Publikasi (FR-04)
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="ticket_price" class="block text-sm font-semibold text-[#2A211A] mb-1">
                            Harga Tiket Tetap dalam Rupiah (IDR) <span class="text-[#8B2E1F]">*</span>
                        </label>
                        <input type="number"
                               id="ticket_price"
                               name="ticket_price"
                               min="0"
                               required
                               value="{{ old('ticket_price', $event?->ticket_price ?? 0) }}"
                               class="input-heritage tabular-nums font-bold text-[#6B3F1E]">
                        <span class="text-xs text-[#6E635A] mt-1 block">
                            Nilai <code>0</code> berarti <strong>TBD</strong> (belum dikonfigurasi). Wajib diisi &gt; 0 sebelum event diaktifkan.
                        </span>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-semibold text-[#2A211A] mb-1">
                            Status Event <span class="text-[#8B2E1F]">*</span>
                        </label>
                        <select id="status" name="status" class="input-heritage">
                            <option value="draft" @selected(old('status', $event?->status ?? 'draft') === 'draft')>Draft (Belum Tampil di Publik)</option>
                            <option value="active" @selected(old('status', $event?->status) === 'active')>Active (Tampil & Dapat Dipesan)</option>
                            <option value="inactive" @selected(old('status', $event?->status) === 'inactive')>Inactive (Diarsipkan)</option>
                        </select>
                        <span class="text-xs text-[#6E635A] mt-1 block">
                            Aturan FR-04: Sistem menolak status <code>Active</code> apabila harga tiket = 0 atau belum memiliki jadwal sesi.
                        </span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-heritage-primary">
                        {{ $isEdit ? 'Simpan Perubahan Event' : 'Simpan & Lanjut ke Jadwal Sesi' }}
                    </button>
                </div>
            </div>
        </form>

        <!-- TAB 2: Manajemen Jadwal Sesi & Kuota (FR-13) -->
        @if($isEdit)
            <div data-tab-panel="schedules" class="{{ $activeTab !== 'schedules' ? 'hidden' : '' }} space-y-6">
                <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4">
                    <h2 class="font-serif text-xl font-bold text-[#2A211A]">Tambah Sesi Jadwal Kunjungan Baru</h2>

                    <form action="{{ route('admin.schedules.store', $event) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-6 gap-4 items-end">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Tanggal Tour</label>
                            <input type="date" name="tour_date" required value="{{ now()->addDays(5)->toDateString() }}" class="input-heritage text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Kode Sesi</label>
                            <input type="text" name="session_code" required placeholder="PAGI / SIANG" class="input-heritage text-sm uppercase">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" required value="09:00" class="input-heritage text-sm tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" required value="11:00" class="input-heritage text-sm tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-[#6E635A] mb-1">Kuota Kursi</label>
                            <input type="number" name="quota_total" required min="1" value="35" class="input-heritage text-sm tabular-nums">
                            <input type="hidden" name="status" value="open">
                        </div>
                        <div>
                            <button type="submit" class="btn-heritage-primary text-xs w-full">
                                + Tambah Sesi
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Schedules Table -->
                <div class="border border-[#E3D9C9] bg-[#FFFFFF] overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                                <th class="py-3 px-4 font-semibold">Tanggal</th>
                                <th class="py-3 px-4 font-semibold">Kode Sesi</th>
                                <th class="py-3 px-4 font-semibold">Waktu</th>
                                <th class="py-3 px-4 font-semibold text-right">Terpesan</th>
                                <th class="py-3 px-4 font-semibold">Ubah Kuota Total & Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E3D9C9]">
                            @forelse($event->schedules as $schedule)
                                <tr>
                                    <td class="py-3 px-4 tabular-nums font-medium">{{ $schedule->tour_date->format('Y-m-d') }}</td>
                                    <td class="py-3 px-4 font-semibold text-[#6B3F1E]">{{ $schedule->session_code }}</td>
                                    <td class="py-3 px-4 tabular-nums">{{ $schedule->formattedTimeRange() }} WIB</td>
                                    <td class="py-3 px-4 text-right tabular-nums font-semibold">{{ $schedule->quota_booked }} Tiket</td>
                                    <td class="py-3 px-4">
                                        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="number"
                                                   name="quota_total"
                                                   min="{{ max(1, $schedule->quota_booked) }}"
                                                   value="{{ $schedule->quota_total }}"
                                                   class="input-heritage text-xs w-24 min-h-[34px] tabular-nums">
                                            <select name="status" class="input-heritage text-xs w-32 min-h-[34px]">
                                                <option value="open" @selected($schedule->status === 'open')>Open</option>
                                                <option value="full" @selected($schedule->status === 'full')>Full</option>
                                                <option value="closed" @selected($schedule->status === 'closed')>Closed</option>
                                            </select>
                                            <button type="submit" class="btn-heritage-secondary text-xs py-1 px-3 min-h-[34px]">
                                                Update Kuota
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-[#6E635A]">Belum ada sesi jadwal pada event ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const triggers = document.querySelectorAll('.tab-trigger');
            const infoPanel = document.querySelector('[data-tab-panel="info"]');
            const pricingPanel = document.querySelector('[data-tab-panel="pricing"]');
            const schedulesPanel = document.querySelector('[data-tab-panel="schedules"]');
            const returnTab = document.getElementById('return_tab');

            triggers.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const target = btn.getAttribute('data-tab-target');
                    if (returnTab) returnTab.value = target;

                    triggers.forEach(function (b) {
                        b.classList.remove('border-[#6B3F1E]', 'text-[#6B3F1E]', 'bg-[#F3EAD8]/50');
                        b.classList.add('border-transparent', 'text-[#6E635A]');
                    });
                    btn.classList.add('border-[#6B3F1E]', 'text-[#6B3F1E]', 'bg-[#F3EAD8]/50');

                    if (target === 'schedules') {
                        if (infoPanel) infoPanel.classList.add('hidden');
                        if (pricingPanel) pricingPanel.classList.add('hidden');
                        if (schedulesPanel) schedulesPanel.classList.remove('hidden');
                    } else {
                        if (infoPanel) infoPanel.classList.remove('hidden');
                        if (pricingPanel) pricingPanel.classList.remove('hidden');
                        if (schedulesPanel) schedulesPanel.classList.add('hidden');
                    }
                });
            });
        });
    </script>
@endsection

