@extends('layouts.admin')

@section('title', 'Kelola Event, Jadwal & Harga — Admin GulaTour')
@section('page_title', 'Manajemen Event Tour, Jadwal Sesi & Kuota')
@section('page_subtitle', 'FR-04 & FR-13: Event tidak dapat diaktifkan sebelum Harga Tiket Tetap (> Rp 0) dan minimal 1 sesi jadwal dikonfigurasi.')

@section('header_actions')
    <a href="{{ route('admin.events.create') }}" class="btn-heritage-primary text-xs py-2 px-4">
        + Buat Event Tour Baru
    </a>
@endsection

@section('content')
    <div class="border border-[#E3D9C9] bg-[#FFFFFF] overflow-x-auto">
        <table class="w-full text-sm text-left border-collapse">
            <thead>
                <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                    <th class="py-3.5 px-4 font-semibold">Judul Event & Slug</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Harga Tiket Tetap (DEC-001)</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Jumlah Sesi</th>
                    <th class="py-3.5 px-4 font-semibold">Status Publikasi</th>
                    <th class="py-3.5 px-4 font-semibold">Kesiapan Aktivasi (FR-04)</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Aksi (3-Tab Editor)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E3D9C9]">
                @foreach($events as $event)
                    <tr class="hover:bg-[#FAF6EE]/60">
                        <td class="py-4 px-4">
                            <strong class="font-serif text-base text-[#2A211A] block">{{ $event->title }}</strong>
                            <code class="text-xs text-[#6E635A]">/events/{{ $event->slug }}</code>
                        </td>
                        <td class="py-4 px-4 text-right tabular-nums font-semibold {{ $event->ticket_price > 0 ? 'text-[#6B3F1E]' : 'text-[#8B2E1F]' }}">
                            {{ $event->formattedPrice() }}
                        </td>
                        <td class="py-4 px-4 text-right tabular-nums">
                            {{ $event->schedules_count }} Sesi
                        </td>
                        <td class="py-4 px-4">
                            @if($event->status === 'active')
                                <span class="px-2.5 py-1 text-xs font-semibold border border-[#4A6B3A] text-[#4A6B3A] bg-[#4A6B3A]/10">Aktif (Publik)</span>
                            @elseif($event->status === 'draft')
                                <span class="px-2.5 py-1 text-xs font-semibold border border-[#B8860B] text-[#B8860B] bg-[#B8860B]/10">Draft (TBD)</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold border border-[#8B2E1F] text-[#8B2E1F] bg-[#8B2E1F]/10">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-xs">
                            @if($event->canBeActivated())
                                <span class="text-[#4A6B3A] font-semibold">✓ Memenuhi Syarat Aktif</span>
                            @else
                                <span class="text-[#8B2E1F] font-semibold">Belum Lengkap (Wajib Harga &gt; 0 &amp; Jadwal)</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('admin.events.edit', ['event' => $event->id, 'tab' => 'info']) }}" class="btn-heritage-secondary text-xs py-1.5 px-3 min-h-[36px]">
                                Edit Info & Jadwal
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

