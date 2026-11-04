@extends('layouts.admin')

@section('title', 'Ringkasan Operasional & Kuota — Admin GulaTour')
@section('page_title', 'Ringkasan Pemesanan, Pendapatan & Kuota Sesi')
@section('page_subtitle', 'Modul M6 • Pantau metrik penjualan tiket, tingkat keterisian sesi, notifikasi baru, dan log audit.')

@section('header_actions')
    <a href="{{ route('api.v1.admin.orders', ['export' => 'csv']) }}" class="btn-heritage-secondary text-xs py-2 px-3">
        Ekspor Laporan CSV
    </a>
    <a href="{{ route('admin.tickets.scanner') }}" class="btn-heritage-primary text-xs py-2 px-4">
        Scan QR E-Ticket
    </a>
@endsection

@section('content')
    <div class="space-y-8">
        <!-- Summary Widgets (Allowed Card Containers per DESIGN.md §8) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5">
                <span class="text-xs uppercase tracking-wider text-[#6E635A] block">Total Pendapatan (Lunas)</span>
                <span class="font-serif text-2xl font-bold text-[#6B3F1E] tabular-nums block mt-1">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </span>
                <span class="text-xs text-[#4A6B3A] mt-1 block">Dari {{ $paidOrdersCount }} transaksi lunas</span>
            </div>

            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5">
                <span class="text-xs uppercase tracking-wider text-[#6E635A] block">Tiket Terjual (Lunas)</span>
                <span class="font-serif text-2xl font-bold text-[#2A211A] tabular-nums block mt-1">
                    {{ $ticketsSoldCount }} Tiket
                </span>
                <span class="text-xs text-[#6E635A] mt-1 block">Siap divalidasi via QR</span>
            </div>

            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5">
                <span class="text-xs uppercase tracking-wider text-[#6E635A] block">Menunggu Pembayaran</span>
                <span class="font-serif text-2xl font-bold text-[#B8860B] tabular-nums block mt-1">
                    {{ $pendingOrdersCount }} Order
                </span>
                <span class="text-xs text-[#6E635A] mt-1 block">Menunggu callback Midtrans</span>
            </div>

            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5">
                <span class="text-xs uppercase tracking-wider text-[#6E635A] block">Notifikasi Baru (FR-16)</span>
                <span class="font-serif text-2xl font-bold text-[#2A211A] tabular-nums block mt-1">
                    {{ $unreadNotificationsCount }} Belum Dibaca
                </span>
                @if($unreadNotificationsCount > 0)
                    <form action="{{ route('admin.notifications.read') }}" method="POST" class="mt-1">
                        @csrf
                        <button type="submit" class="text-xs text-[#6B3F1E] underline cursor-pointer">Tandai semua dibaca</button>
                    </form>
                @else
                    <span class="text-xs text-[#6E635A] mt-1 block">Semua notifikasi terbaca</span>
                @endif
            </div>
        </div>

        <!-- Tabel Occupancy per Sesi (DESIGN.md §6 & FR-11) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-serif text-xl font-bold text-[#2A211A]">Tabel Tingkat Keterisian Kuota per Sesi (Occupancy)</h2>
                <a href="{{ route('admin.events.index') }}" class="link-heritage-tertiary text-xs">Kelola Jadwal & Kuota &rarr;</a>
            </div>

            <div class="overflow-x-auto border border-[#E3D9C9] bg-[#FFFFFF]">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                            <th class="py-3 px-4 font-semibold">Event Tour</th>
                            <th class="py-3 px-4 font-semibold">Tanggal</th>
                            <th class="py-3 px-4 font-semibold">Sesi & Jam</th>
                            <th class="py-3 px-4 font-semibold text-right">Terpesan</th>
                            <th class="py-3 px-4 font-semibold text-right">Total Kuota</th>
                            <th class="py-3 px-4 font-semibold text-right">Sisa Kuota</th>
                            <th class="py-3 px-4 font-semibold">Tingkat Keterisian</th>
                            <th class="py-3 px-4 font-semibold">Status Sesi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3D9C9]">
                        @foreach($schedules as $schedule)
                            @php
                                $pct = $schedule->quota_total > 0
                                    ? min(100, round(($schedule->quota_booked / $schedule->quota_total) * 100))
                                    : 0;
                            @endphp
                            <tr class="hover:bg-[#FAF6EE]/60">
                                <td class="py-3 px-4 font-medium text-[#2A211A]">{{ $schedule->event->title ?? '-' }}</td>
                                <td class="py-3 px-4 tabular-nums">{{ $schedule->tour_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-4 tabular-nums">{{ $schedule->session_code }} ({{ $schedule->formattedTimeRange() }})</td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold">{{ $schedule->quota_booked }}</td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ $schedule->quota_total }}</td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold {{ $schedule->remainingQuota() > 0 ? 'text-[#4A6B3A]' : 'text-[#8B2E1F]' }}">
                                    {{ $schedule->remainingQuota() }}
                                </td>
                                <td class="py-3 px-4 w-44">
                                    <div class="flex items-center gap-2 tabular-nums text-xs">
                                        <div class="w-full h-2 bg-[#FAF6EE] border border-[#E3D9C9]">
                                            <div class="h-full {{ $pct >= 100 ? 'bg-[#8B2E1F]' : 'bg-[#6B3F1E]' }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="w-9 text-right">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-xs font-semibold {{ $schedule->isAvailable() ? 'text-[#4A6B3A]' : 'text-[#8B2E1F]' }}">
                                    {{ $schedule->statusLabel() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Split: Notifications (FR-16) & Audit Logs (NFR Audit) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-6 space-y-3">
                <h2 class="font-serif text-xl font-bold text-[#2A211A]">Notifikasi Pemesanan & Pembayaran Masuk (FR-16)</h2>
                <div class="border border-[#E3D9C9] bg-[#FFFFFF] divide-y divide-[#E3D9C9]">
                    @forelse($notifications as $notif)
                        <div class="p-4 flex items-start justify-between gap-4 {{ !$notif->is_read ? 'bg-[#F3EAD8]/50' : '' }}">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    @if(!$notif->is_read)
                                        <span class="w-2 h-2 rounded-full bg-[#C8A24A]"></span>
                                    @endif
                                    <strong class="text-sm text-[#2A211A]">{{ $notif->title }}</strong>
                                </div>
                                <p class="text-xs text-[#6E635A]">{{ $notif->message }}</p>
                            </div>
                            <span class="text-[11px] text-[#6E635A] tabular-nums whitespace-nowrap">
                                {{ $notif->created_at?->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-[#6E635A]">Belum ada notifikasi.</div>
                    @endforelse
                </div>
            </div>

            <div class="lg:col-span-6 space-y-3">
                <h2 class="font-serif text-xl font-bold text-[#2A211A]">Log Audit Aktivitas Admin (NFR Audit)</h2>
                <div class="border border-[#E3D9C9] bg-[#FFFFFF] overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] uppercase tracking-wider text-[#6E635A]">
                                <th class="py-2.5 px-3 font-semibold">Waktu</th>
                                <th class="py-2.5 px-3 font-semibold">Admin</th>
                                <th class="py-2.5 px-3 font-semibold">Aksi</th>
                                <th class="py-2.5 px-3 font-semibold">Perubahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E3D9C9]">
                            @forelse($auditLogs as $log)
                                <tr>
                                    <td class="py-2.5 px-3 tabular-nums text-[#6E635A]">{{ $log->created_at?->format('d/m H:i') }}</td>
                                    <td class="py-2.5 px-3 font-medium text-[#2A211A]">{{ $log->user?->name ?? 'System' }}</td>
                                    <td class="py-2.5 px-3 font-mono text-[#6B3F1E]">{{ $log->action }}</td>
                                    <td class="py-2.5 px-3 text-[#6E635A] font-mono">
                                        {{ json_encode($log->new_values) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-[#6E635A]">Belum ada aktivitas terekam.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

