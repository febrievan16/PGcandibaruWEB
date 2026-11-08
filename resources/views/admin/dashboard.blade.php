@extends('layouts.admin')

@section('title', 'Ringkasan Operasional & Kuota — Admin GulaTour')
@section('page_title', 'Ringkasan Pemesanan, Pendapatan & Kuota Sesi')
@section('page_subtitle', 'Modul M6 • Pantau metrik penjualan tiket, grafik kunjungan bulanan, proporsi kuota event, dan log audit.')

@section('header_actions')
    <a href="{{ route('api.v1.admin.orders', ['export' => 'csv']) }}" class="btn-heritage-secondary text-xs py-2 px-3 rounded-md">
        Ekspor Laporan CSV
    </a>
    <a href="{{ route('admin.tickets.scanner') }}" class="btn-heritage-primary text-xs py-2 px-4 rounded-md">
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
    <style>
        .dash-grad-coral {
            background: linear-gradient(90deg, #ffbf96 0%, #fe7096 100%);
        }
        .dash-grad-blue {
            background: linear-gradient(90deg, #90caf9 0%, #047edf 99%);
        }
        .dash-grad-teal {
            background: linear-gradient(90deg, #84d9d2 0%, #07cdae 100%);
        }
        .dash-grad-purple {
            background: linear-gradient(135deg, #da8cff 0%, #9a55ff 100%);
        }
        .dash-circle-top {
            position: absolute;
            right: -32px;
            top: -26px;
            width: 175px;
            height: 175px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.22);
            pointer-events: none;
        }
        .dash-circle-bottom {
            position: absolute;
            right: 16px;
            bottom: -56px;
            width: 160px;
            height: 160px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.18);
            pointer-events: none;
        }
    </style>

    <div class="space-y-7">
        <!-- Modern Dashboard Sub-Header Bar (Matching Reference Style) -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="dash-grad-purple w-10 h-10 rounded-lg text-white inline-flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-[#2A211A] tracking-tight">Dashboard Ringkasan &amp; Kuota</h2>
                    <p class="text-xs text-[#6E635A]">Ikhtisar analitik pendapatan, tiket terjual, dan keterisian jadwal sesi secara real-time</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($unreadNotificationsCount > 0)
                    <form action="{{ route('admin.notifications.read') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-full bg-[#fe7096]/15 text-[#d6336c] border border-[#fe7096]/40 text-xs font-semibold hover:bg-[#fe7096]/25 transition-colors cursor-pointer">
                            {{ $unreadNotificationsCount }} Notifikasi Baru &bull; Tandai Dibaca
                        </button>
                    </form>
                @endif
                <span class="text-xs font-semibold text-[#6E635A] inline-flex items-center gap-1.5">
                    <span>Overview</span>
                    <svg class="w-4 h-4 text-[#9a55ff]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </span>
            </div>
        </div>

        <!-- 3 Vibrant Gradient Summary Cards with Translucent Circle Overlays -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Total Pendapatan (Coral-Pink Gradient) -->
            <div class="dash-grad-coral relative overflow-hidden rounded-xl p-7 text-white shadow-sm">
                <div class="dash-circle-top" aria-hidden="true"></div>
                <div class="dash-circle-bottom" aria-hidden="true"></div>

                <div class="relative z-10 flex items-center justify-between">
                    <span class="text-base font-medium tracking-wide opacity-95">Total Pendapatan (Lunas)</span>
                    <svg class="w-6 h-6 opacity-90 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 14l4-4 4 4 5-6"/>
                    </svg>
                </div>

                <div class="relative z-10 mt-3 mb-7">
                    <span class="text-3xl font-bold tracking-tight tabular-nums block">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </span>
                </div>

                <div class="relative z-10 text-xs font-semibold tracking-wide opacity-95">
                    Dari {{ $paidOrdersCount }} transaksi lunas &bull; Terverifikasi
                </div>
            </div>

            <!-- Card 2: Tiket Terjual & Order (Ocean-Blue Gradient) -->
            <div class="dash-grad-blue relative overflow-hidden rounded-xl p-7 text-white shadow-sm">
                <div class="dash-circle-top" aria-hidden="true"></div>
                <div class="dash-circle-bottom" aria-hidden="true"></div>

                <div class="relative z-10 flex items-center justify-between">
                    <span class="text-base font-medium tracking-wide opacity-95">Tiket Terjual (Lunas)</span>
                    <svg class="w-6 h-6 opacity-90 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </div>

                <div class="relative z-10 mt-3 mb-7">
                    <span class="text-3xl font-bold tracking-tight tabular-nums block">
                        {{ number_format($ticketsSoldCount, 0, ',', '.') }} Tiket
                    </span>
                </div>

                <div class="relative z-10 text-xs font-semibold tracking-wide opacity-95">
                    Siap divalidasi via QR &bull; {{ $pendingOrdersCount }} Order Menunggu Pembayaran
                </div>
            </div>

            <!-- Card 3: Tingkat Keterisian Kuota & Notifikasi (Emerald-Teal Gradient) -->
            <div class="dash-grad-teal relative overflow-hidden rounded-xl p-7 text-white shadow-sm">
                <div class="dash-circle-top" aria-hidden="true"></div>
                <div class="dash-circle-bottom" aria-hidden="true"></div>

                <div class="relative z-10 flex items-center justify-between">
                    <span class="text-base font-medium tracking-wide opacity-95">Tingkat Keterisian Kuota</span>
                    <svg class="w-6 h-6 opacity-90 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 9l10 13L22 9 12 2z"/>
                    </svg>
                </div>

                <div class="relative z-10 mt-3 mb-7">
                    <span class="text-3xl font-bold tracking-tight tabular-nums block">
                        {{ $overallOccupancyPct ?? 0 }}% Terisi
                    </span>
                </div>

                <div class="relative z-10 text-xs font-semibold tracking-wide opacity-95">
                    {{ $totalQuotaBooked ?? 0 }} / {{ $totalQuotaCapacity ?? 0 }} kursi terpesan &bull; {{ $unreadNotificationsCount }} Belum Dibaca
                </div>
            </div>
        </div>

        <!-- Middle Analytics Row: Grouped Bar Chart (7/12) + Donut Chart (5/12) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left 7/12: Visit And Sales Statistics (Grouped Bar Chart) -->
            <div class="lg:col-span-7 bg-[#FFFFFF] rounded-xl border border-[#E3D9C9]/80 p-6 shadow-2xs flex flex-col justify-between">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-base font-bold text-[#2A211A]">Statistik Kunjungan &amp; Penjualan Bulanan</h3>
                        <p class="text-xs text-[#6E635A] mt-0.5">Perbandingan tiket terjual, transaksi lunas, dan pesanan pending (8 bulan terakhir)</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-[#6E635A]">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-[#a461d8] inline-block"></span>
                            <span>Tiket</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-[#fe7096] inline-block"></span>
                            <span>Lunas</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-[#36a2eb] inline-block"></span>
                            <span>Pending</span>
                        </span>
                    </div>
                </div>

                <!-- Bar Chart Grid -->
                <div class="relative pt-4 pb-2">
                    <!-- Subtle Horizontal Grid Lines -->
                    <div class="absolute inset-x-0 top-4 bottom-8 flex flex-col justify-between pointer-events-none" aria-hidden="true">
                        <div class="border-b border-dashed border-[#E3D9C9]/60 w-full"></div>
                        <div class="border-b border-dashed border-[#E3D9C9]/60 w-full"></div>
                        <div class="border-b border-dashed border-[#E3D9C9]/60 w-full"></div>
                        <div class="border-b border-dashed border-[#E3D9C9]/60 w-full"></div>
                    </div>

                    <div class="relative z-10 grid grid-cols-8 gap-2 sm:gap-3 items-end h-56 pt-4">
                        @foreach($monthlyChartData ?? [] as $mItem)
                            <div class="group relative flex flex-col items-center justify-end h-full">
                                <!-- Hover Tooltip -->
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none absolute -top-12 left-1/2 -translate-x-1/2 z-20 bg-[#2A211A] text-[#FAF6EE] text-[11px] rounded-md px-2.5 py-1.5 whitespace-nowrap shadow-md">
                                    <strong class="block text-[#C8A24A]">{{ $mItem['full_label'] }}</strong>
                                    <span>Tiket: {{ $mItem['tickets_sold'] }} &bull; Lunas: {{ $mItem['paid_orders'] }} &bull; Pending: {{ $mItem['pending_orders'] }}</span>
                                </div>

                                <!-- 3 Grouped Vertical Bars -->
                                <div class="w-full h-44 flex items-end justify-center gap-1 sm:gap-1.5 px-1 border-b border-[#E3D9C9]">
                                    <div class="w-2 sm:w-2.5 rounded-t-sm bg-[#a461d8] transition-all duration-300 group-hover:brightness-110"
                                         style="height: {{ $mItem['tickets_pct'] }}%"
                                         title="Tiket Terjual: {{ $mItem['tickets_sold'] }}"></div>
                                    <div class="w-2 sm:w-2.5 rounded-t-sm bg-[#36a2eb] transition-all duration-300 group-hover:brightness-110"
                                         style="height: {{ $mItem['pending_pct'] }}%"
                                         title="Order Pending: {{ $mItem['pending_orders'] }}"></div>
                                    <div class="w-2 sm:w-2.5 rounded-t-sm bg-[#fe7096] transition-all duration-300 group-hover:brightness-110"
                                         style="height: {{ $mItem['paid_pct'] }}%"
                                         title="Transaksi Lunas: {{ $mItem['paid_orders'] }}"></div>
                                </div>

                                <span class="text-xs font-semibold text-[#6E635A] mt-2.5 tracking-wider tabular-nums">
                                    {{ $mItem['label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right 5/12: Proporsi Keterisian per Event Tour (Donut Chart) -->
            <div class="lg:col-span-5 bg-[#FFFFFF] rounded-xl border border-[#E3D9C9]/80 p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#2A211A]">Proporsi Kuota Event Tour</h3>
                    <p class="text-xs text-[#6E635A] mt-0.5">Distribusi keterisian kursi berdasarkan program kunjungan</p>
                </div>

                <!-- SVG Donut Chart -->
                <div class="relative flex items-center justify-center my-4">
                    <svg class="w-44 h-44 -rotate-90" viewBox="0 0 36 36" role="img" aria-label="Grafik Donat Proporsi Kuota Event Tour">
                        <circle cx="18" cy="18" r="13.5" fill="none" stroke="#F3EAD8" stroke-width="5.2"></circle>
                        @foreach($eventDistribution ?? [] as $segment)
                            <circle cx="18"
                                    cy="18"
                                    r="13.5"
                                    fill="none"
                                    stroke="{{ $segment['color'] }}"
                                    stroke-width="5.2"
                                    stroke-dasharray="{{ $segment['dash_array'] }}"
                                    stroke-dashoffset="{{ $segment['dash_offset'] }}"
                                    class="transition-all duration-300"></circle>
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                        <span class="text-xl font-bold text-[#2A211A] tabular-nums">{{ $totalQuotaBooked ?? 0 }}</span>
                        <span class="text-xs text-[#6E635A]">Kursi Terisi</span>
                    </div>
                </div>

                <!-- Legend List with Percentages -->
                <div class="space-y-3 pt-2 border-t border-[#E3D9C9]/60">
                    @forelse($eventDistribution ?? [] as $segment)
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $segment['color'] }}"></span>
                                <span class="text-[#2A211A] font-medium truncate" title="{{ $segment['title'] }}">
                                    {{ $segment['title'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 tabular-nums">
                                <span class="text-[#6E635A]">({{ $segment['booked'] }}/{{ $segment['capacity'] }})</span>
                                <span class="font-bold text-[#2A211A] w-9 text-right">{{ $segment['percentage'] }}%</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[#6E635A] text-center py-2">Belum ada jadwal event terdaftar.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Tabel Occupancy per Sesi (DESIGN.md §6 & FR-11) -->
        <div id="occupancy-section" class="bg-[#FFFFFF] rounded-xl border border-[#E3D9C9]/80 p-6 shadow-2xs space-y-4">
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
                <a href="{{ route('admin.events.index') }}" class="link-heritage-tertiary text-xs">Kelola Jadwal &amp; Kuota &rarr;</a>
            </div>

            <!-- Filter Bar: Event Tour, Status Sesi, & Tampilkan Semua Data -->
            <form id="occupancy-filter-form"
                  action="{{ route('admin.dashboard') }}#occupancy-section"
                  method="GET"
                  class="rounded-lg border border-[#E3D9C9] bg-[#FAF6EE]/60 p-4 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-5">
                    <label for="occupancy_event_filter" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">
                        Filter Berdasarkan Event Tour
                    </label>
                    <select id="occupancy_event_filter"
                            name="event_id"
                            class="input-heritage text-xs min-h-[38px] bg-[#FFFFFF]">
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
                            class="input-heritage text-xs min-h-[38px] bg-[#FFFFFF]">
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
                    <button type="submit" class="btn-heritage-primary text-xs py-2 px-3 min-h-[38px] rounded-md cursor-pointer">
                        Terapkan
                    </button>
                    <button type="button"
                            id="occupancy-reset-all-btn"
                            class="btn-heritage-secondary text-xs py-2 px-3 min-h-[38px] rounded-md cursor-pointer w-full">
                        Tampilkan Semua Data
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto rounded-lg border border-[#E3D9C9] bg-[#FFFFFF]">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                            <th class="py-3 px-4 font-semibold">Event Tour</th>
                            <th class="py-3 px-4 font-semibold">Tanggal</th>
                            <th class="py-3 px-4 font-semibold">Sesi &amp; Jam</th>
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
                                <td class="py-3 px-4 text-right tabular-nums font-semibold {{ $schedule->remainingQuota() > 0 ? 'text-[#07cdae]' : 'text-[#fe7096]' }}">
                                    {{ $schedule->remainingQuota() }}
                                </td>
                                <td class="py-3 px-4 w-44">
                                    <div class="flex items-center gap-2 tabular-nums text-xs">
                                        <div class="w-full h-2 rounded-full bg-[#FAF6EE] border border-[#E3D9C9] overflow-hidden">
                                            <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-[#fe7096]' : 'bg-[#047edf]' }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="w-9 text-right font-semibold">{{ $pct }}%</span>
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
            <div class="lg:col-span-6 bg-[#FFFFFF] rounded-xl border border-[#E3D9C9]/80 p-6 shadow-2xs space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="font-serif text-lg font-bold text-[#2A211A]">Notifikasi Pemesanan &amp; Pembayaran Masuk (FR-16)</h2>
                    @if($unreadNotificationsCount > 0)
                        <form action="{{ route('admin.notifications.read') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-[#6B3F1E] underline cursor-pointer">Tandai semua dibaca</button>
                        </form>
                    @endif
                </div>
                <div class="rounded-lg border border-[#E3D9C9] bg-[#FFFFFF] divide-y divide-[#E3D9C9]">
                    @forelse($notifications as $notif)
                        <div class="p-4 flex items-start justify-between gap-4 {{ !$notif->is_read ? 'bg-[#F3EAD8]/50' : '' }}">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    @if(!$notif->is_read)
                                        <span class="w-2 h-2 rounded-full bg-[#fe7096]"></span>
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

            <div class="lg:col-span-6 bg-[#FFFFFF] rounded-xl border border-[#E3D9C9]/80 p-6 shadow-2xs space-y-3">
                <h2 class="font-serif text-lg font-bold text-[#2A211A]">Log Audit Aktivitas Admin (NFR Audit)</h2>
                <div class="rounded-lg border border-[#E3D9C9] bg-[#FFFFFF] overflow-x-auto">
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
