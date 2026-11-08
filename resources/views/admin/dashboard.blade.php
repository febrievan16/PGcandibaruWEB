@extends('layouts.admin')

@section('title', 'Ringkasan Operasional & Kuota — Admin GulaTour')
@section('page_title', 'Ringkasan Pemesanan, Pendapatan & Kuota Sesi')
@section('page_subtitle', 'Modul M6 • Pantau metrik penjualan tiket, tingkat keterisian sesi, notifikasi baru, dan log audit.')

@section('header_actions')
    {{-- <a href="{{ route('admin.transactions.index') }}" class="btn-heritage-secondary text-xs py-2 px-3">
        Laporan Transaksi Bulanan
    </a> --}}
    <a href="{{ route('api.v1.admin.orders', ['export' => 'csv']) }}" class="btn-heritage-secondary text-xs py-2 px-3">
        Ekspor Laporan CSV
    </a>
    <a href="{{ route('admin.tickets.scanner') }}" class="btn-heritage-primary text-xs py-2 px-4">
        Scan QR E-Ticket
    </a>
@endsection

@section('content')
    <div class="space-y-8">
        <!-- Summary KPI Cards in Bento Heritage Style -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Pendapatan Lunas (Highlighted Warm Heritage Card) -->
            <div class="border border-[#C8A24A]/70 bg-gradient-to-br from-[#FAF0DC] to-[#F3EAD8] p-5 flex flex-col justify-between shadow-xs">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs uppercase tracking-wider text-[#6B3F1E] font-semibold">Total Pendapatan (Lunas)</span>
                    <div class="w-8 h-8 rounded-full bg-[#6B3F1E] text-[#FAF6EE] flex items-center justify-center shrink-0 shadow-xs" aria-hidden="true">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M14.8 9A2 2 0 0 0 13 8h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1-1.8-1"/>
                            <path d="M12 6v2m0 8v2"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="font-serif text-2xl lg:text-3xl font-bold text-[#6B3F1E] tabular-nums block">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs text-[#4A6B3A] font-medium mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#4A6B3A]"></span>
                        Dari {{ $paidOrdersCount }} transaksi lunas
                    </span>
                </div>
            </div>

            <!-- 2. Tiket Terjual Lunas -->
            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5 flex flex-col justify-between hover:border-[#6B3F1E] transition-colors shadow-xs">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs uppercase tracking-wider text-[#6E635A] font-semibold">Tiket Terjual (Lunas)</span>
                    <div class="w-8 h-8 rounded-full bg-[#FAF6EE] border border-[#E3D9C9] text-[#6B3F1E] flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="6" width="18" height="12" rx="2"/>
                            <line x1="8" y1="6" x2="8" y2="18" stroke-dasharray="2 2"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="font-serif text-2xl lg:text-3xl font-bold text-[#2A211A] tabular-nums block">
                        {{ $ticketsSoldCount }} <span class="text-base font-sans font-normal text-[#6E635A]">Tiket</span>
                    </span>
                    <span class="inline-flex items-center gap-1 text-xs text-[#4A6B3A] font-medium mt-1">
                        Siap divalidasi via Scanner QR
                    </span>
                </div>
            </div>

            <!-- 3. Menunggu Pembayaran -->
            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5 flex flex-col justify-between hover:border-[#B8860B] transition-colors shadow-xs">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs uppercase tracking-wider text-[#6E635A] font-semibold">Menunggu Pembayaran</span>
                    <div class="w-8 h-8 rounded-full bg-[#FFF9E6] border border-[#E3D9C9] text-[#B8860B] flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/>
                            <polyline points="12 7 12 12 15 15"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="font-serif text-2xl lg:text-3xl font-bold text-[#B8860B] tabular-nums block">
                        {{ $pendingOrdersCount }} <span class="text-base font-sans font-normal text-[#6E635A]">Order</span>
                    </span>
                    <span class="text-xs text-[#6E635A] mt-1 block">
                        Menunggu penyelesaian Midtrans
                    </span>
                </div>
            </div>

            <!-- 4. Notifikasi Baru & Audit Log -->
            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5 flex flex-col justify-between hover:border-[#6B3F1E] transition-colors shadow-xs">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs uppercase tracking-wider text-[#6E635A] font-semibold">Notifikasi Baru</span>
                    <div class="w-8 h-8 rounded-full bg-[#FAF6EE] border border-[#E3D9C9] text-[#6B3F1E] flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="font-serif text-2xl lg:text-3xl font-bold text-[#2A211A] tabular-nums block">
                        {{ $unreadNotificationsCount }} <span class="text-base font-sans font-normal text-[#6E635A]">Belum Dibaca</span>
                    </span>
                    @if($unreadNotificationsCount > 0)
                        <form action="{{ route('admin.notifications.read') }}" method="POST" class="mt-1">
                            @csrf
                            <button type="submit" class="text-xs text-[#6B3F1E] font-medium underline hover:text-[#2A211A] cursor-pointer">Tandai semua dibaca</button>
                        </form>
                    @else
                        <span class="text-xs text-[#6E635A] mt-1 block">Semua notifikasi terbaca</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabel Occupancy per Sesi (DESIGN.md §6 & FR-11) -->
        <div id="occupancy-section" class="space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div>
                    <h2 class="font-serif text-xl font-bold text-[#2A211A]">Tabel Tingkat Keterisian Kuota per Sesi (Occupancy)</h2>
                    <span id="occupancy-count-badge" class="text-xs text-[#6E635A] tabular-nums">
                        Menampilkan {{ $schedules->count() }} dari {{ $schedules->count() }} sesi jadwal
                    </span>
                </div>
                <a href="{{ route('admin.events.index') }}" class="link-heritage-tertiary text-xs inline-flex items-center gap-1 group">
                    <span>Kelola Jadwal &amp; Kuota</span>
                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            <!-- Filter Bar: Event Tour, Status Sesi, & Tampilkan Semua Data -->
            <form id="occupancy-filter-form"
                  action="{{ route('admin.dashboard') }}#occupancy-section"
                  method="GET"
                  class="border border-[#E3D9C9] bg-[#FFFFFF] p-4 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-5">
                    <label for="occupancy_event_filter" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">
                        Filter Berdasarkan Event Tour
                    </label>
                    <select id="occupancy_event_filter"
                            name="event_id"
                            class="input-heritage text-xs min-h-[38px]">
                        <option value="all" @selected(($filterEventId ?? 'all') === 'all' || ($filterEventId ?? '') === '')>
                            Tampilkan Semua Event Tour
                        </option>
                        @foreach($occupancyEvents ?? collect() as $ev)
                            <option value="{{ $ev->id }}" @selected((string) ($filterEventId ?? '') === (string) $ev->id)>
                                {{ $ev->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label for="occupancy_status_filter" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">
                        Filter Berdasarkan Status Sesi
                    </label>
                    <select id="occupancy_status_filter"
                            name="session_status"
                            class="input-heritage text-xs min-h-[38px]">
                        <option value="all" @selected(($filterSessionStatus ?? 'all') === 'all' || ($filterSessionStatus ?? '') === '')>
                            Tampilkan Semua Status Sesi
                        </option>
                        <option value="available" @selected(($filterSessionStatus ?? '') === 'available')>
                            Tersedia (Open)
                        </option>
                        <option value="full" @selected(($filterSessionStatus ?? '') === 'full')>
                            Kuota Penuh (Full)
                        </option>
                        <option value="past" @selected(($filterSessionStatus ?? '') === 'past')>
                            Sesi Berakhir (Lampau)
                        </option>
                        <option value="closed" @selected(($filterSessionStatus ?? '') === 'closed')>
                            Ditutup (Closed)
                        </option>
                    </select>
                </div>

                <div class="sm:col-span-3 flex items-center gap-2">
                    <button type="submit" class="btn-heritage-primary text-xs py-2 px-3 min-h-[38px] cursor-pointer">
                        Terapkan
                    </button>
                    <button type="button"
                            id="occupancy-reset-all-btn"
                            class="btn-heritage-secondary text-xs py-2 px-3 min-h-[38px] cursor-pointer w-full">
                        Tampilkan Semua Data
                    </button>
                </div>
            </form>

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
                    <tbody id="occupancy-table-body" class="divide-y divide-[#E3D9C9]">
                        @foreach($schedules as $schedule)
                            @php
                                $pct = $schedule->quota_total > 0
                                    ? min(100, round(($schedule->quota_booked / $schedule->quota_total) * 100))
                                    : 0;
                                $stateKey = $schedule->occupancyStateKey();
                                $matchesEvent = (($filterEventId ?? 'all') === 'all' || ($filterEventId ?? '') === '' || (string) $schedule->event_id === (string) $filterEventId);
                                $matchesStatus = (($filterSessionStatus ?? 'all') === 'all' || ($filterSessionStatus ?? '') === '' || $stateKey === $filterSessionStatus);
                                $isVisible = ($matchesEvent && $matchesStatus);
                            @endphp
                            <tr data-occupancy-row
                                data-occupancy-event="{{ $schedule->event_id }}"
                                data-occupancy-status="{{ $stateKey }}"
                                class="hover:bg-[#FAF6EE]/60 {{ !$isVisible ? 'hidden' : '' }}">
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
                        <tr id="occupancy-empty-row" class="hidden">
                            <td colspan="8" class="py-12 px-4 text-center">
                                <div class="max-w-md mx-auto space-y-2">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-[#FAF6EE] border border-[#E3D9C9] flex items-center justify-center text-[#A67C52]" aria-hidden="true">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="8"/>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                        </svg>
                                    </div>
                                    <p class="font-serif text-base font-semibold text-[#2A211A]">Tidak Ada Sesi yang Cocok</p>
                                    <p class="text-xs text-[#6E635A]">Tidak ada jadwal sesi tour yang sesuai dengan filter Event atau Status Sesi yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const eventSelect = document.getElementById('occupancy_event_filter');
                const statusSelect = document.getElementById('occupancy_status_filter');
                const resetBtn = document.getElementById('occupancy-reset-all-btn');
                const rows = document.querySelectorAll('[data-occupancy-row]');
                const emptyRow = document.getElementById('occupancy-empty-row');
                const countBadge = document.getElementById('occupancy-count-badge');

                function applyOccupancyFilter() {
                    const selectedEvent = eventSelect ? eventSelect.value : 'all';
                    const selectedStatus = statusSelect ? statusSelect.value : 'all';
                    let visibleCount = 0;

                    rows.forEach(function (row) {
                        const rowEvent = row.getAttribute('data-occupancy-event');
                        const rowStatus = row.getAttribute('data-occupancy-status');
                        const matchEvent = (selectedEvent === 'all' || selectedEvent === '' || rowEvent === selectedEvent);
                        const matchStatus = (selectedStatus === 'all' || selectedStatus === '' || rowStatus === selectedStatus);

                        if (matchEvent && matchStatus) {
                            row.classList.remove('hidden');
                            visibleCount++;
                        } else {
                            row.classList.add('hidden');
                        }
                    });

                    if (emptyRow) {
                        emptyRow.classList.toggle('hidden', visibleCount > 0);
                    }

                    if (countBadge) {
                        countBadge.textContent = 'Menampilkan ' + visibleCount + ' dari ' + rows.length + ' sesi jadwal';
                    }
                }

                if (eventSelect) {
                    eventSelect.addEventListener('change', applyOccupancyFilter);
                }

                if (statusSelect) {
                    statusSelect.addEventListener('change', applyOccupancyFilter);
                }

                if (resetBtn) {
                    resetBtn.addEventListener('click', function () {
                        if (eventSelect) eventSelect.value = 'all';
                        if (statusSelect) statusSelect.value = 'all';
                        applyOccupancyFilter();
                        if (window.history && window.history.replaceState) {
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }
                    });
                }

                applyOccupancyFilter();
            });
        </script>

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

