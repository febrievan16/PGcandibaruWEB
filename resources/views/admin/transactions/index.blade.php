@extends('layouts.admin')

@section('title', 'Laporan Bulanan & Data Transaksi — Admin GulaTour')
@section('page_title', 'Laporan Bulanan, Visualisasi Data & Deskripsi Transaksi')
@section('page_subtitle', 'Analisis komprehensif transaksi tiap pemesan, peringkat event paling sering dipesan, dan rekapitulasi pendapatan bulanan PT Pabrik Gula Candi Baru.')

@section('header_actions')
    <button type="button"
            id="toggle-print-preview-btn"
            class="btn-heritage-secondary text-xs py-2 px-3 cursor-pointer">
        Pratinjau Dokumen Cetak
    </button>
    <button type="button"
            id="open-print-dialog-btn"
            class="btn-heritage-primary text-xs py-2 px-4 cursor-pointer">
        Cetak Laporan Bulanan (PDF)
    </button>
    <a href="{{ route('api.v1.admin.orders', ['export' => 'csv']) }}" class="btn-heritage-secondary text-xs py-2 px-3">
        Ekspor CSV
    </a>
@endsection

@section('content')
    @php
        $paidOrders = $allOrders->where('status', 'paid');
        $pendingOrders = $allOrders->where('status', 'pending');
        $voidOrders = $allOrders->whereIn('status', ['expired', 'cancelled']);

        $grandTotalRevenue = (int) $paidOrders->sum('total_amount');
        $grandTotalTickets = (int) $paidOrders->sum('ticket_qty');
        $totalTicketsRequestedAll = (int) $allOrders->sum('ticket_qty');
        $avgOrderValue = $paidOrders->count() > 0 ? (int) round($grandTotalRevenue / $paidOrders->count()) : 0;
        $overallConversionPct = $allOrders->count() > 0
            ? (int) round(($paidOrders->count() / $allOrders->count()) * 100)
            : 0;

        $topEvent = $eventRankings->first();
        $topBuyer = $buyerSummaries->first();

        $registeredBuyersCount = $buyerSummaries->where('is_registered', true)->count();
        $registeredBuyerPct = $buyerSummaries->count() > 0
            ? (int) round(($registeredBuyersCount / $buyerSummaries->count()) * 100)
            : 0;

        $reportPeriodLabel = $selectedMonth !== ''
            ? \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $selectedMonth . '-01')->translatedFormat('F Y')
            : 'Rekapitulasi 6 Bulan Terakhir (Semua Periode)';
        $docNumber = 'LAP-GT/' . now()->format('Y/m') . '/' . str_pad((string) $allOrders->count(), 3, '0', STR_PAD_LEFT);

        // Compute SVG Spline Coordinates for the last 6 months in $monthlyReport
        $chartMonths = $monthlyReport->slice(-6)->values();
        $chartCount = max(1, $chartMonths->count());
        $xStart = 55;
        $xEnd = 575;
        $yTop = 28;
        $yBottom = 142;
        $xStep = $chartCount > 1 ? ($xEnd - $xStart) / ($chartCount - 1) : 0;

        $revPoints = [];
        $tktPoints = [];
        $peakIdx = 0;
        $peakRev = -1;

        foreach ($chartMonths as $idx => $cm) {
            $cx = round($xStart + ($idx * $xStep), 1);
            $revRatio = $maxMonthlyRevenue > 0 ? ($cm['revenue'] / $maxMonthlyRevenue) : 0;
            $tktRatio = $maxMonthlyTickets > 0 ? ($cm['tickets_sold'] / $maxMonthlyTickets) : 0;

            $cyRev = round($yBottom - ($revRatio * ($yBottom - $yTop)), 1);
            $cyTkt = round($yBottom - ($tktRatio * ($yBottom - $yTop - 18)), 1);

            if ($cm['revenue'] >= $peakRev) {
                $peakRev = $cm['revenue'];
                $peakIdx = $idx;
            }

            $revPoints[] = ['x' => $cx, 'y' => $cyRev, 'data' => $cm];
            $tktPoints[] = ['x' => $cx, 'y' => $cyTkt, 'data' => $cm];
        }

        $buildSmoothSvgPath = function (array $pts): string {
            if (count($pts) === 0) {
                return '';
            }
            if (count($pts) === 1) {
                return "M {$pts[0]['x']},{$pts[0]['y']}";
            }
            $d = "M {$pts[0]['x']},{$pts[0]['y']}";
            for ($i = 0; $i < count($pts) - 1; $i++) {
                $p0 = $pts[$i];
                $p1 = $pts[$i + 1];
                $midX = round(($p0['x'] + $p1['x']) / 2, 1);
                $d .= " C {$midX},{$p0['y']} {$midX},{$p1['y']} {$p1['x']},{$p1['y']}";
            }
            return $d;
        };

        $revenueSplinePath = $buildSmoothSvgPath($revPoints);
        $ticketSplinePath = $buildSmoothSvgPath($tktPoints);
        $firstX = $revPoints[0]['x'] ?? $xStart;
        $lastX = $revPoints[count($revPoints) - 1]['x'] ?? $xEnd;
        $revenueAreaPath = $revenueSplinePath !== ''
            ? "{$revenueSplinePath} L {$lastX},{$yBottom} L {$firstX},{$yBottom} Z"
            : '';
        $peakPoint = $revPoints[$peakIdx] ?? ['x' => 300, 'y' => 60, 'data' => ['revenue' => 0, 'short_label' => '-']];

        // Semi-circle gauge arc (total half-circle arc length for r=38 is pi * 38 = 119.38)
        $gaugeCircumference = 119.38;
        $gaugeDash = round(($overallConversionPct / 100) * $gaugeCircumference, 1);

        // Donut segments for Event Rankings (r=13.5 => circumference = 84.823)
        $heritageDonutColors = ['#6B3F1E', '#C8A24A', '#A67C52', '#4A6B3A', '#8B2E1F'];
        $donutCircumference = 84.823;
        $donutOffset = 0.0;
        $eventDonutSegments = $eventRankings->map(function (array $ev, int $idx) use ($heritageDonutColors, $donutCircumference, &$donutOffset): array {
            $pct = max(0, (int) $ev['revenue_share_pct']);
            $arc = round(($pct / 100) * $donutCircumference, 2);
            $seg = [
                'title' => $ev['title'],
                'event_id' => $ev['event_id'],
                'revenue' => $ev['revenue'],
                'tickets_sold' => $ev['tickets_sold'],
                'total_orders' => $ev['total_orders'],
                'pct' => $pct,
                'color' => $heritageDonutColors[$idx % count($heritageDonutColors)],
                'dash_array' => "{$arc} {$donutCircumference}",
                'dash_offset' => (string) round(-$donutOffset, 2),
            ];
            $donutOffset += $arc;
            return $seg;
        });
    @endphp

    <!-- ===================================================================== -->
    <!-- A. INTERACTIVE WEB DASHBOARD VIEW (Hidden automatically during print) -->
    <!-- ===================================================================== -->
    <div id="interactive-report-dashboard" class="space-y-6 no-print">

        <!-- ================================================================= -->
        <!-- BENTO ROW 1: Left 9/12 (3 KPIs + Line Chart + Gauge) & Right 3/12 -->
        <!-- ================================================================= -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-stretch">

            <!-- Left 9/12 Column Stack -->
            <div class="xl:col-span-9 flex flex-col gap-5">

                <!-- Sub-Row 1A: 3 KPI Summary Cards (EzMart Layout + Heritage Palette) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <!-- KPI 1: Highlighted Warm Heritage Gold-Cream Card -->
                    <div class="rounded-2xl bg-gradient-to-br from-[#F3EAD8] to-[#FAF0DC] border border-[#D8C5A8] p-5 flex flex-col justify-between shadow-2xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-[#6E635A]">Total Pendapatan Lunas</span>
                            <span class="w-9 h-9 rounded-xl bg-[#6B3F1E] text-[#FAF6EE] inline-flex items-center justify-center shadow-xs shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 9.5c-.5-1-1.5-1.5-2.5-1.5-1.5 0-2.5.8-2.5 2s1 1.8 2.5 2.2 2.5 1 2.5 2.3-1 2-2.5 2c-1 0-2-.5-2.5-1.5M12 6.5v11"></path>
                                </svg>
                            </span>
                        </div>
                        <div class="mt-4 flex items-end justify-between gap-2">
                            <div>
                                <span class="font-serif text-2xl font-bold text-[#6B3F1E] tabular-nums tracking-tight block">
                                    Rp {{ number_format($grandTotalRevenue, 0, ',', '.') }}
                                </span>
                                <span class="text-xs text-[#6E635A] mt-0.5 block tabular-nums">
                                    Rata-rata Rp {{ number_format($avgOrderValue, 0, ',', '.') }}/order
                                </span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-block px-2 py-0.5 rounded-md bg-[#4A6B3A]/15 text-[#4A6B3A] text-xs font-bold tabular-nums">
                                    {{ $paidOrders->count() }} Lunas
                                </span>
                                <span class="block text-xs text-[#6E635A] mt-0.5">Terverifikasi</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 2: White Card — Total Orders -->
                    <div class="rounded-2xl bg-[#FFFFFF] border border-[#E3D9C9] p-5 flex flex-col justify-between shadow-2xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-[#6E635A]">Total Pesanan Masuk</span>
                            <span class="w-9 h-9 rounded-xl bg-[#FAF6EE] border border-[#E3D9C9] text-[#6B3F1E] inline-flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.4 5.6A1 1 0 006.6 20h10.8a1 1 0 001-.8L20 13M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path>
                                </svg>
                            </span>
                        </div>
                        <div class="mt-4 flex items-end justify-between gap-2">
                            <div>
                                <span class="font-serif text-2xl font-bold text-[#2A211A] tabular-nums tracking-tight block">
                                    {{ number_format($allOrders->count(), 0, ',', '.') }} Order
                                </span>
                                <span class="text-xs text-[#6E635A] mt-0.5 block tabular-nums">
                                    {{ $pendingOrders->count() }} pending &bull; {{ $voidOrders->count() }} batal
                                </span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-block px-2 py-0.5 rounded-md bg-[#C8A24A]/20 text-[#6B3F1E] text-xs font-bold tabular-nums">
                                    {{ $overallConversionPct }}% Rasio
                                </span>
                                <span class="block text-xs text-[#6E635A] mt-0.5">Konversi lunas</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 3: White Card — Total Tickets & Visitors -->
                    <div class="rounded-2xl bg-[#FFFFFF] border border-[#E3D9C9] p-5 flex flex-col justify-between shadow-2xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-[#6E635A]">Total Tiket &amp; Peserta</span>
                            <span class="w-9 h-9 rounded-xl bg-[#FAF6EE] border border-[#E3D9C9] text-[#6B3F1E] inline-flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"></path>
                                </svg>
                            </span>
                        </div>
                        <div class="mt-4 flex items-end justify-between gap-2">
                            <div>
                                <span class="font-serif text-2xl font-bold text-[#2A211A] tabular-nums tracking-tight block">
                                    {{ number_format($grandTotalTickets, 0, ',', '.') }} Tiket
                                </span>
                                <span class="text-xs text-[#6E635A] mt-0.5 block tabular-nums">
                                    Dari {{ $buyerSummaries->count() }} pemesan unik
                                </span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-block px-2 py-0.5 rounded-md bg-[#4A6B3A]/15 text-[#4A6B3A] text-xs font-bold tabular-nums">
                                    +{{ $registeredBuyerPct }}% Akun
                                </span>
                                <span class="block text-xs text-[#6E635A] mt-0.5">Terdaftar</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sub-Row 1B: Revenue Spline Analytics (8/12) + Semi-Circle Conversion Gauge (4/12) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 flex-1">
                    <!-- Left 8/12: Revenue Analytics Dual-Line Spline Chart -->
                    <div class="lg:col-span-8 rounded-2xl bg-[#FFFFFF] border border-[#E3D9C9] p-6 flex flex-col justify-between shadow-2xs">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                                    Revenue Analytics &bull; Laporan Bulanan
                                </span>
                                <h2 class="font-serif text-lg font-bold text-[#2A211A] mt-0.5">
                                    Grafik Tren Pendapatan & Penjualan Tiket Bulanan (6 Bulan Terakhir)
                                </h2>
                                <div class="flex flex-wrap items-center gap-4 text-xs text-[#6E635A] mt-1.5">
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-[#6B3F1E]">
                                        <span class="w-4 h-0.5 bg-[#6B3F1E] inline-block"></span>
                                        <span>Pendapatan Lunas (IDR)</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-[#C8A24A]">
                                        <span class="w-4 h-0.5 border-b-2 border-dashed border-[#C8A24A] inline-block"></span>
                                        <span>Volume Tiket Terjual</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Quick Period Filter Pill -->
                            <form action="{{ route('admin.transactions.index') }}" method="GET" class="shrink-0">
                                <select name="month"
                                        onchange="this.form.submit()"
                                        aria-label="Pilih Periode Bulan"
                                        class="px-3 py-1.5 rounded-lg bg-[#6B3F1E] text-[#FAF6EE] text-xs font-semibold cursor-pointer border border-[#6B3F1E] focus:outline-none">
                                    <option value="" @selected($selectedMonth === '')>6 Bulan Terakhir (Semua)</option>
                                    @foreach($monthlyReport->reverse() as $mOpt)
                                        <option value="{{ $mOpt['month_key'] }}" @selected($selectedMonth === $mOpt['month_key'])>
                                            {{ $mOpt['month_label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>

                        <!-- Dual-Line Smooth Spline SVG Chart -->
                        <div class="relative mt-4 pt-3">
                            <svg class="w-full h-52 overflow-visible" viewBox="0 0 610 175" role="img" aria-label="Grafik Kurva Tren Pendapatan dan Tiket Bulanan">
                                <defs>
                                    <linearGradient id="heritageRevenueGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#C8A24A" stop-opacity="0.30"/>
                                        <stop offset="100%" stop-color="#C8A24A" stop-opacity="0.0"/>
                                    </linearGradient>
                                </defs>

                                <!-- Horizontal Dashed Gridlines & Y-Axis Scale -->
                                <line x1="48" y1="28" x2="585" y2="28" stroke="#E3D9C9" stroke-dasharray="3 3"/>
                                <line x1="48" y1="66" x2="585" y2="66" stroke="#E3D9C9" stroke-dasharray="3 3"/>
                                <line x1="48" y1="104" x2="585" y2="104" stroke="#E3D9C9" stroke-dasharray="3 3"/>
                                <line x1="48" y1="142" x2="585" y2="142" stroke="#E3D9C9"/>

                                <text x="0" y="32" fill="#6E635A" font-size="10" class="tabular-nums">{{ number_format($maxMonthlyRevenue / 1000, 0, ',', '.') }}k</text>
                                <text x="0" y="70" fill="#6E635A" font-size="10" class="tabular-nums">{{ number_format(($maxMonthlyRevenue * 0.66) / 1000, 0, ',', '.') }}k</text>
                                <text x="0" y="108" fill="#6E635A" font-size="10" class="tabular-nums">{{ number_format(($maxMonthlyRevenue * 0.33) / 1000, 0, ',', '.') }}k</text>
                                <text x="0" y="145" fill="#6E635A" font-size="10">0</text>

                                <!-- Shaded Area under Revenue Curve -->
                                @if($revenueAreaPath !== '')
                                    <path d="{{ $revenueAreaPath }}" fill="url(#heritageRevenueGrad)"/>
                                @endif

                                <!-- Dashed Ticket Volume Curve (#C8A24A) -->
                                @if($ticketSplinePath !== '')
                                    <path d="{{ $ticketSplinePath }}" fill="none" stroke="#C8A24A" stroke-width="2.2" stroke-dasharray="5 5" stroke-linecap="round"/>
                                @endif

                                <!-- Solid Revenue Curve (#6B3F1E) -->
                                @if($revenueSplinePath !== '')
                                    <path d="{{ $revenueSplinePath }}" fill="none" stroke="#6B3F1E" stroke-width="3" stroke-linecap="round"/>
                                @endif

                                <!-- Peak Point Vertical Guide & Callout Badge -->
                                @php
                                    $calloutX = min(520, max(95, $peakPoint['x']));
                                    $calloutY = max(2, $peakPoint['y'] - 38);
                                @endphp
                                <line x1="{{ $peakPoint['x'] }}" y1="{{ $peakPoint['y'] }}" x2="{{ $peakPoint['x'] }}" y2="{{ $yBottom }}" stroke="#C8A24A" stroke-dasharray="3 3"/>
                                <rect x="{{ $calloutX - 52 }}" y="{{ $calloutY - 12 }}" width="104" height="30" rx="6" fill="#FFFFFF" stroke="#D8C5A8" stroke-width="1.2"/>
                                <text x="{{ $calloutX }}" y="{{ $calloutY - 1 }}" text-anchor="middle" fill="#6E635A" font-size="8.5">Puncak ({{ $peakPoint['data']['short_label'] ?? '' }})</text>
                                <text x="{{ $calloutX }}" y="{{ $calloutY + 11 }}" text-anchor="middle" fill="#6B3F1E" font-size="10" font-weight="bold">Rp {{ number_format($peakPoint['data']['revenue'] ?? 0, 0, ',', '.') }}</text>

                                <!-- Data Nodes & X-Axis Clickable Labels -->
                                @foreach($revPoints as $idx => $pt)
                                    @php
                                        $mData = $pt['data'];
                                        $isCurrentFilter = ($selectedMonth === $mData['month_key']);
                                    @endphp
                                    <a href="{{ route('admin.transactions.index', ['month' => $isCurrentFilter ? '' : $mData['month_key']]) }}#tabel-rincian-transaksi">
                                        <circle cx="{{ $pt['x'] }}"
                                                cy="{{ $pt['y'] }}"
                                                r="{{ $idx === $peakIdx || $isCurrentFilter ? '5' : '3.8' }}"
                                                fill="{{ $isCurrentFilter ? '#6B3F1E' : '#FAF6EE' }}"
                                                stroke="#6B3F1E"
                                                stroke-width="2.5">
                                            <title>{{ $mData['month_label'] }}: Rp {{ number_format($mData['revenue'], 0, ',', '.') }} ({{ $mData['tickets_sold'] }} tiket, {{ $mData['total_orders'] }} order)</title>
                                        </circle>
                                        <text x="{{ $pt['x'] }}"
                                              y="164"
                                              text-anchor="middle"
                                              fill="{{ $isCurrentFilter || $idx === $peakIdx ? '#6B3F1E' : '#6E635A' }}"
                                              font-weight="{{ $isCurrentFilter || $idx === $peakIdx ? 'bold' : 'normal' }}"
                                              font-size="10.5">
                                            {{ $mData['short_label'] }}
                                        </text>
                                    </a>
                                @endforeach
                            </svg>
                        </div>
                    </div>

                    <!-- Right 4/12: Monthly Target & Conversion Semi-Circle Gauge -->
                    <div class="lg:col-span-4 rounded-2xl bg-[#FFFFFF] border border-[#E3D9C9] p-6 flex flex-col justify-between shadow-2xs">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">Target &amp; Rasio</span>
                                <h3 class="font-serif text-lg font-bold text-[#2A211A]">Realisasi Konversi</h3>
                            </div>
                            <span class="px-2.5 py-1 rounded-md bg-[#F3EAD8] text-[#6B3F1E] text-xs font-bold tabular-nums">
                                {{ $paidOrders->count() }}/{{ $allOrders->count() }} Order
                            </span>
                        </div>

                        <!-- Semi-Circle Arch Gauge -->
                        <div class="flex flex-col items-center my-2">
                            <div class="relative w-48 h-28 flex items-end justify-center overflow-hidden">
                                <svg class="w-48 h-48" viewBox="0 0 100 100" role="img" aria-label="Gauge Konversi Pelunasan {{ $overallConversionPct }}%">
                                    <path d="M 12 52 A 38 38 0 0 1 88 52" fill="none" stroke="#F3EAD8" stroke-width="10" stroke-linecap="round"/>
                                    <path d="M 12 52 A 38 38 0 0 1 88 52" fill="none" stroke="#6B3F1E" stroke-width="10" stroke-linecap="round" stroke-dasharray="{{ $gaugeDash }} 125"/>
                                </svg>
                                <div class="absolute bottom-0.5 text-center">
                                    <span class="font-serif text-2xl font-bold text-[#2A211A] tabular-nums block">{{ $overallConversionPct }}%</span>
                                    <span class="text-xs font-semibold text-[#4A6B3A]">Rasio Pelunasan</span>
                                </div>
                            </div>

                            <p class="text-xs font-bold text-[#2A211A] mt-3">
                                {{ $overallConversionPct >= 70 ? 'Performa Konversi Sangat Baik!' : 'Pantau Tagihan Menunggu Pembayaran' }}
                            </p>
                            <p class="text-xs text-[#6E635A] text-center mt-1 leading-relaxed">
                                {{ $paidOrders->count() }} pesanan lunas menghasilkan {{ $grandTotalTickets }} e-ticket aktif dari total {{ $allOrders->count() }} transaksi.
                            </p>
                        </div>

                        <!-- Bottom 2-Column Heritage Summary Box -->
                        <div class="grid grid-cols-2 rounded-xl bg-[#F3EAD8]/75 border border-[#D8C5A8] p-3 text-center divide-x divide-[#D8C5A8]">
                            <div class="px-1">
                                <span class="text-xs text-[#6E635A] block">Tagihan Pending</span>
                                <strong class="text-xs font-bold text-[#B8860B] tabular-nums mt-0.5 block">
                                    Rp {{ number_format((int) $pendingOrders->sum('total_amount'), 0, ',', '.') }}
                                </strong>
                            </div>
                            <div class="px-1">
                                <span class="text-xs text-[#6E635A] block">Realisasi Lunas</span>
                                <strong class="text-xs font-bold text-[#6B3F1E] tabular-nums mt-0.5 block">
                                    Rp {{ number_format($grandTotalRevenue, 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right 3/12 Full-Height Card: Top Categories / Peringkat Event Paling Sering Dipesan -->
            <div class="xl:col-span-3 rounded-2xl bg-[#FFFFFF] border border-[#E3D9C9] p-6 flex flex-col justify-between shadow-2xs">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Top Categories</span>
                        <a href="#rincian-peringkat-event" class="text-xs font-semibold text-[#6B3F1E] hover:underline">Lihat Detail</a>
                    </div>
                    <h2 class="font-serif text-lg font-bold text-[#2A211A] mt-0.5">
                        Peringkat Event Paling Sering Dipesan
                    </h2>
                </div>

                <!-- SVG Ring Donut Chart with Center Total Revenue -->
                <div class="relative flex items-center justify-center my-4">
                    <svg class="w-44 h-44 -rotate-90" viewBox="0 0 36 36" role="img" aria-label="Grafik Donat Pendapatan per Event Tour">
                        <circle cx="18" cy="18" r="13.5" fill="none" stroke="#F3EAD8" stroke-width="4.8"></circle>
                        @foreach($eventDonutSegments as $seg)
                            <circle cx="18"
                                    cy="18"
                                    r="13.5"
                                    fill="none"
                                    stroke="{{ $seg['color'] }}"
                                    stroke-width="4.8"
                                    stroke-dasharray="{{ $seg['dash_array'] }}"
                                    stroke-dashoffset="{{ $seg['dash_offset'] }}"></circle>
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4 pointer-events-none">
                        <span class="text-xs text-[#6E635A]">Total Penjualan</span>
                        <strong class="font-serif text-sm font-bold text-[#2A211A] tabular-nums mt-0.5">
                            Rp {{ number_format($grandTotalRevenue, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>

                <!-- Event Breakdown List -->
                <div class="space-y-3 pt-3 border-t border-[#E3D9C9]">
                    @forelse($eventDonutSegments as $seg)
                        <div class="flex items-start justify-between gap-2 text-xs">
                            <a href="{{ route('admin.transactions.index', ['event_id' => $seg['event_id']]) }}#tabel-rincian-transaksi"
                               class="inline-flex items-start gap-2 min-w-0 hover:text-[#6B3F1E]">
                                <span class="w-2.5 h-2.5 rounded-xs shrink-0 mt-1" style="background-color: {{ $seg['color'] }}"></span>
                                <span class="min-w-0">
                                    <strong class="text-[#2A211A] font-medium block truncate" title="{{ $seg['title'] }}">{{ $seg['title'] }}</strong>
                                    <span class="text-[#6E635A] tabular-nums">{{ $seg['total_orders'] }}x order &bull; {{ $seg['tickets_sold'] }} tiket</span>
                                </span>
                            </a>
                            <div class="text-right tabular-nums shrink-0">
                                <strong class="text-[#6B3F1E] block">Rp {{ number_format($seg['revenue'], 0, ',', '.') }}</strong>
                                <span class="text-[#6E635A]">{{ $seg['pct'] }}%</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[#6E635A] text-center py-2">Belum ada data event.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- ================================================================= -->
        <!-- BENTO ROW 2: Top Buyers (3/12) + 5-Step Funnel (6/12) + Channels  -->
        <!-- ================================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">

            <!-- Left 3/12: Active User / 5 Pemesan dengan Kontribusi Tertinggi -->
            <div class="lg:col-span-3 rounded-2xl bg-[#FFFFFF] border border-[#E3D9C9] p-6 flex flex-col justify-between shadow-2xs">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">Loyalitas Pengunjung</span>
                            <h2 class="font-serif text-lg font-bold text-[#2A211A] mt-0.5">5 Pemesan Kontribusi Tertinggi</h2>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-[#4A6B3A]/15 text-[#4A6B3A] text-xs font-bold tabular-nums shrink-0">
                            {{ $buyerSummaries->count() }} Pemesan
                        </span>
                    </div>

                    <div class="space-y-3.5 mt-4">
                        @php
                            $buyerBarColors = ['#6B3F1E', '#A67C52', '#C8A24A', '#4A6B3A', '#8C6239'];
                        @endphp
                        @forelse($buyerSummaries->take(5) as $idx => $buyer)
                            @php
                                $buyerSharePct = $grandTotalRevenue > 0
                                    ? min(100, (int) round(($buyer['total_spent'] / $grandTotalRevenue) * 100))
                                    : 0;
                                $barColor = $buyerBarColors[$idx % count($buyerBarColors)];
                            @endphp
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-[#2A211A] truncate" title="{{ $buyer['buyer_name'] }} ({{ $buyer['buyer_email'] }})">
                                        #{{ $idx + 1 }} {{ $buyer['buyer_name'] }}
                                    </span>
                                    <strong class="text-[#6B3F1E] tabular-nums shrink-0">{{ $buyerSharePct }}%</strong>
                                </div>
                                <div class="w-full h-2 rounded-full bg-[#F3EAD8] overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ max(8, $buyerSharePct) }}%; background-color: {{ $barColor }}"></div>
                                </div>
                                <div class="flex justify-between text-[#6E635A] tabular-nums">
                                    <span>{{ $buyer['total_tickets_all'] }} tiket ({{ $buyer['total_orders'] }}x order)</span>
                                    <span>Rp {{ number_format($buyer['total_spent'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-[#6E635A]">Belum ada data pemesan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-[#E3D9C9] flex items-center justify-between text-xs text-[#6E635A]">
                    <span>Pemesan #1: <strong class="text-[#2A211A]">{{ $topBuyer['buyer_name'] ?? '-' }}</strong></span>
                    <a href="#top-10-pemesan-section" class="text-[#6B3F1E] font-semibold hover:underline">Top 10 &rarr;</a>
                </div>
            </div>

            <!-- Middle 6/12: Conversion Rate 5-Column Funnel -->
            <div class="lg:col-span-6 rounded-2xl bg-[#FFFFFF] border border-[#E3D9C9] p-6 flex flex-col justify-between shadow-2xs">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <div>
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">Conversion Funnel</span>
                        <h2 class="font-serif text-lg font-bold text-[#2A211A] mt-0.5">Alur Konversi Pemesanan &amp; E-Ticket</h2>
                    </div>
                    <span class="px-3 py-1 rounded-lg bg-[#F3EAD8] border border-[#D8C5A8] text-[#6B3F1E] text-xs font-semibold">
                        {{ $reportPeriodLabel }}
                    </span>
                </div>

                <!-- 5 Funnel Steps Matching Reference Image -->
                <div class="grid grid-cols-2 sm:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-[#E3D9C9] gap-2 items-end pt-2">
                    <!-- Step 1: Tiket Diminta -->
                    <div class="px-2 pt-2 sm:pt-0 flex flex-col justify-between h-48">
                        <div>
                            <span class="text-xs text-[#6E635A] block leading-tight">Total Tiket Diminta</span>
                            <strong class="font-serif text-xl font-bold text-[#2A211A] tabular-nums block mt-1">{{ $totalTicketsRequestedAll }}</strong>
                            <span class="inline-block px-1.5 py-0.5 rounded bg-[#4A6B3A]/15 text-[#4A6B3A] text-xs font-bold mt-1">100%</span>
                        </div>
                        <div class="w-full h-24 rounded-t-xl bg-[#F3EAD8] border-t-2 border-[#D8C5A8]"></div>
                    </div>

                    <!-- Step 2: Order Dibuat -->
                    <div class="px-2 pt-2 sm:pt-0 flex flex-col justify-between h-48">
                        <div>
                            <span class="text-xs text-[#6E635A] block leading-tight">Pesanan Dibuat</span>
                            <strong class="font-serif text-xl font-bold text-[#2A211A] tabular-nums block mt-1">{{ $allOrders->count() }}</strong>
                            <span class="inline-block px-1.5 py-0.5 rounded bg-[#C8A24A]/20 text-[#6B3F1E] text-xs font-bold mt-1">Order</span>
                        </div>
                        <div class="w-full h-20 rounded-t-xl bg-[#E8D5B5] border-t-2 border-[#C8A24A]"></div>
                    </div>

                    <!-- Step 3: Order Lunas -->
                    <div class="px-2 pt-2 sm:pt-0 flex flex-col justify-between h-48">
                        <div>
                            <span class="text-xs text-[#6E635A] block leading-tight">Transaksi Lunas</span>
                            <strong class="font-serif text-xl font-bold text-[#2A211A] tabular-nums block mt-1">{{ $paidOrders->count() }}</strong>
                            <span class="inline-block px-1.5 py-0.5 rounded bg-[#4A6B3A]/15 text-[#4A6B3A] text-xs font-bold mt-1">{{ $overallConversionPct }}%</span>
                        </div>
                        <div class="w-full h-16 rounded-t-xl bg-[#D9B87C] border-t-2 border-[#A67C52]"></div>
                    </div>

                    <!-- Step 4: Tiket Terbit -->
                    <div class="px-2 pt-2 sm:pt-0 flex flex-col justify-between h-48">
                        <div>
                            <span class="text-xs text-[#6E635A] block leading-tight">E-Ticket Terbit</span>
                            <strong class="font-serif text-xl font-bold text-[#4A6B3A] tabular-nums block mt-1">{{ $grandTotalTickets }}</strong>
                            <span class="inline-block px-1.5 py-0.5 rounded bg-[#4A6B3A]/15 text-[#4A6B3A] text-xs font-bold mt-1">Aktif</span>
                        </div>
                        <div class="w-full h-12 rounded-t-xl bg-[#A67C52] border-t-2 border-[#6B3F1E]"></div>
                    </div>

                    <!-- Step 5: Pending / Batal -->
                    <div class="px-2 pt-2 sm:pt-0 flex flex-col justify-between h-48">
                        <div>
                            <span class="text-xs text-[#6E635A] block leading-tight">Pending / Batal</span>
                            <strong class="font-serif text-xl font-bold text-[#8B2E1F] tabular-nums block mt-1">{{ $pendingOrders->count() + $voidOrders->count() }}</strong>
                            <span class="inline-block px-1.5 py-0.5 rounded bg-[#8B2E1F]/15 text-[#8B2E1F] text-xs font-bold mt-1">{{ 100 - $overallConversionPct }}%</span>
                        </div>
                        <div class="w-full h-7 rounded-t-xl bg-[#6B3F1E]"></div>
                    </div>
                </div>
            </div>

            <!-- Right 3/12: Payment Channel Distribution (Traffic Sources Stacked Bar Style) -->
            <div class="lg:col-span-3 rounded-2xl bg-[#FFFFFF] border border-[#E3D9C9] p-6 flex flex-col justify-between shadow-2xs">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Kanal Midtrans</span>
                        <span class="text-xs text-[#6E635A] tabular-nums">{{ $paidOrders->count() }} Lunas</span>
                    </div>
                    <h2 class="font-serif text-lg font-bold text-[#2A211A] mt-0.5">
                        Distribusi Metode Pembayaran Lunas
                    </h2>

                    <!-- Segmented Horizontal Stacked Bar -->
                    @php
                        $channelShades = ['#F3EAD8', '#D9B87C', '#A67C52', '#6B3F1E', '#4A6B3A'];
                    @endphp
                    <div class="flex gap-1 h-8 rounded-lg overflow-hidden my-4 border border-[#E3D9C9] bg-[#FAF6EE]">
                        @forelse($paymentChannelStats as $cIdx => $stat)
                            <div class="h-full transition-all"
                                 style="width: {{ max(8, $stat['share_pct']) }}%; background-color: {{ $channelShades[$cIdx % count($channelShades)] }}"
                                 title="{{ $stat['channel'] }}: {{ $stat['share_pct'] }}%"></div>
                        @empty
                            <div class="w-full h-full bg-[#F3EAD8]"></div>
                        @endforelse
                    </div>

                    <div class="space-y-2.5 text-xs">
                        @forelse($paymentChannelStats as $cIdx => $stat)
                            <div class="flex items-center justify-between gap-2 tabular-nums">
                                <span class="inline-flex items-center gap-2 text-[#2A211A] font-medium truncate">
                                    <span class="w-2.5 h-2.5 rounded-xs border border-[#6B3F1E]/40 shrink-0"
                                          style="background-color: {{ $channelShades[$cIdx % count($channelShades)] }}"></span>
                                    <span class="truncate">{{ $stat['channel'] }}</span>
                                </span>
                                <div class="text-right shrink-0">
                                    <strong class="text-[#2A211A]">{{ $stat['share_pct'] }}%</strong>
                                    <span class="text-[#6E635A] block text-[11px]">Rp {{ number_format($stat['revenue'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-[#6E635A]">Belum ada data kanal pembayaran lunas.</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-[#E3D9C9] text-xs text-[#6E635A] flex justify-between">
                    <span>Total Tiket Lunas</span>
                    <strong class="text-[#4A6B3A] tabular-nums">{{ $grandTotalTickets }} E-Ticket</strong>
                </div>
            </div>

        </div>

        <!-- ================================================================= -->
        <!-- 3. Rincian Tabel Bulanan & Peringkat Event Lengkap                -->
        <!-- ================================================================= -->
        <div id="rincian-peringkat-event" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left 7/12: Detailed Monthly Breakdown Table -->
            <div class="lg:col-span-7 rounded-2xl border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4 shadow-2xs">
                <div class="border-b border-[#E3D9C9] pb-3 flex items-center justify-between gap-2">
                    <div>
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">Rekapitulasi Tabel Bulanan</span>
                        <h3 class="font-serif text-xl font-bold text-[#2A211A] mt-0.5">Rincian Performa Transaksi per Bulan</h3>
                    </div>
                    <span class="text-xs text-[#6E635A]">Klik bulan untuk memfilter tabel bawah</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-[#E3D9C9]">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                                <th class="py-3 px-3.5 font-semibold">Periode Bulan</th>
                                <th class="py-3 px-3.5 font-semibold text-right">Order</th>
                                <th class="py-3 px-3.5 font-semibold text-right">Status</th>
                                <th class="py-3 px-3.5 font-semibold text-right">Tiket</th>
                                <th class="py-3 px-3.5 font-semibold text-right">Pendapatan Lunas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E3D9C9]">
                            @foreach($monthlyReport->reverse() as $m)
                                <tr class="hover:bg-[#FAF6EE]/60 {{ $selectedMonth === $m['month_key'] ? 'bg-[#F3EAD8]/60' : '' }}">
                                    <td class="py-3 px-3.5 font-serif font-bold text-[#2A211A]">
                                        <a href="{{ route('admin.transactions.index', ['month' => $m['month_key']]) }}#tabel-rincian-transaksi"
                                           class="hover:text-[#6B3F1E] underline-offset-4 hover:underline">
                                            {{ $m['month_label'] }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-3.5 text-right tabular-nums font-semibold">{{ $m['total_orders'] }}</td>
                                    <td class="py-3 px-3.5 text-right tabular-nums text-xs">
                                        <span class="text-[#4A6B3A] font-semibold">{{ $m['paid_orders'] }} Lunas</span> &bull;
                                        <span class="text-[#B8860B]">{{ $m['pending_orders'] }} Pending</span>
                                    </td>
                                    <td class="py-3 px-3.5 text-right tabular-nums font-bold text-[#2A211A]">{{ $m['tickets_sold'] }}</td>
                                    <td class="py-3 px-3.5 text-right tabular-nums font-serif font-bold text-[#6B3F1E]">
                                        Rp {{ number_format($m['revenue'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-[#6B3F1E] bg-[#F3EAD8] font-bold text-[#2A211A] text-xs">
                                <td class="py-3 px-3.5 uppercase tracking-wider">Total Akumulasi</td>
                                <td class="py-3 px-3.5 text-right tabular-nums">{{ $allOrders->count() }}</td>
                                <td class="py-3 px-3.5 text-right tabular-nums">{{ $paidOrders->count() }} Lunas / {{ $pendingOrders->count() }} Pending</td>
                                <td class="py-3 px-3.5 text-right tabular-nums">{{ $grandTotalTickets }} Tiket</td>
                                <td class="py-3 px-3.5 text-right tabular-nums font-serif text-sm text-[#6B3F1E]">
                                    Rp {{ number_format($grandTotalRevenue, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Right 5/12: Detailed Event Ranking Cards -->
            <div class="lg:col-span-5 rounded-2xl border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4 shadow-2xs">
                <div class="border-b border-[#E3D9C9] pb-3">
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">Analisis Program Tour</span>
                    <h3 class="font-serif text-xl font-bold text-[#2A211A] mt-0.5">Rincian Popularitas per Event</h3>
                </div>

                <div class="space-y-3">
                    @foreach($eventRankings as $rankIndex => $ev)
                        <div class="rounded-xl border {{ $rankIndex === 0 ? 'border-[#6B3F1E] bg-[#F3EAD8]/40' : 'border-[#E3D9C9] bg-[#FAF6EE]' }} p-3.5 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $rankIndex === 0 ? 'bg-[#6B3F1E] text-[#FAF6EE]' : 'bg-[#E3D9C9] text-[#2A211A]' }}">
                                        #{{ $rankIndex + 1 }} {{ $rankIndex === 0 ? 'Terlaris' : '' }}
                                    </span>
                                    <h4 class="font-serif text-sm font-bold text-[#2A211A] mt-1">
                                        <a href="{{ route('admin.transactions.index', ['event_id' => $ev['event_id']]) }}#tabel-rincian-transaksi" class="hover:text-[#6B3F1E]">
                                            {{ $ev['title'] }}
                                        </a>
                                    </h4>
                                </div>
                                <div class="text-right tabular-nums shrink-0">
                                    <strong class="font-serif text-sm font-bold text-[#6B3F1E] block">Rp {{ number_format($ev['revenue'], 0, ',', '.') }}</strong>
                                    <span class="text-xs text-[#6E635A]">{{ $ev['tickets_sold'] }} tiket ({{ $ev['ticket_share_pct'] }}%)</span>
                                </div>
                            </div>
                            <div class="w-full h-2 rounded-full bg-[#FFFFFF] border border-[#E3D9C9] overflow-hidden">
                                <div class="h-full rounded-full bg-[#6B3F1E]" style="width: {{ min(100, max(6, $ev['ticket_share_pct'])) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 4. Ringkasan Deskripsi Aktivitas Transaksi per Pemesan -->
        <div id="top-10-pemesan-section" class="rounded-2xl border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4 shadow-2xs">
            <div class="border-b border-[#E3D9C9] pb-4 flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                        Profil & Rekapitulasi Pemesan
                    </span>
                    <h2 class="font-serif text-2xl font-bold text-[#2A211A] mt-0.5">
                        Ringkasan Akumulasi Transaksi dari Tiap Pemesan (10 Pemesan Tertinggi dari {{ $buyerSummaries->count() }})
                    </h2>
                </div>
                <span class="text-xs text-[#6E635A]">Menampilkan 10 pemesan volume transaksi tertinggi, akumulasi pesanan, dan deskripsi aktivitas</span>
            </div>

            <div class="overflow-x-auto border border-[#E3D9C9]">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                            <th class="py-3 px-4 font-semibold">Nama Pemesan & Kontak</th>
                            <th class="py-3 px-4 font-semibold">Status Akun</th>
                            <th class="py-3 px-4 font-semibold text-right">Frekuensi</th>
                            <th class="py-3 px-4 font-semibold text-right">Total Tiket</th>
                            <th class="py-3 px-4 font-semibold text-right">Total Pembayaran</th>
                            <th class="py-3 px-4 font-semibold">Deskripsi Aktivitas & Event yang Dipesan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3D9C9]">
                        @foreach($buyerSummaries->take(10) as $buyer)
                            <tr class="hover:bg-[#FAF6EE]/60">
                                <td class="py-3.5 px-4">
                                    <strong class="text-[#2A211A] block">{{ $buyer['buyer_name'] }}</strong>
                                    <span class="text-xs text-[#6E635A] tabular-nums">{{ $buyer['buyer_email'] }} &bull; {{ $buyer['buyer_phone'] }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if($buyer['is_registered'])
                                        <span class="px-2 py-0.5 border border-[#4A6B3A] text-[#4A6B3A] bg-[#4A6B3A]/10 font-semibold">Akun Terdaftar</span>
                                    @else
                                        <span class="px-2 py-0.5 border border-[#E3D9C9] bg-[#FAF6EE] text-[#6E635A] font-semibold">Tamu (Guest)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums font-semibold">
                                    {{ $buyer['total_orders'] }}x Order
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums font-bold text-[#2A211A]">
                                    {{ $buyer['total_tickets_all'] }} Tiket
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums font-bold text-[#6B3F1E]">
                                    Rp {{ number_format($buyer['total_spent'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-[#2A211A] leading-relaxed max-w-[44ch]">
                                    {{ $buyer['description'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Tabel Rincian & Deskripsi Lengkap Tiap Transaksi (Filterable) -->
        <div id="tabel-rincian-transaksi" class="rounded-2xl border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-5 shadow-2xs">
            <div class="border-b border-[#E3D9C9] pb-4 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                        Log Rincian Transaksi & Narasi Lengkap
                    </span>
                    <h2 class="font-serif text-2xl font-bold text-[#2A211A] mt-0.5">
                        Deskripsi Rinci Setiap Transaksi Pemesan ({{ $filteredOrders->count() }} Transaksi)
                    </h2>
                </div>
            </div>

            <!-- Filter Bar -->
            <form action="{{ route('admin.transactions.index') }}#tabel-rincian-transaksi"
                  method="GET"
                  class="bg-[#FAF6EE] border border-[#E3D9C9] p-4 grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                <div>
                    <label for="filter_month" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">Periode Bulan</label>
                    <select id="filter_month" name="month" class="input-heritage text-xs min-h-[38px] bg-[#FFFFFF]">
                        <option value="">Semua Bulan</option>
                        @foreach($monthlyReport->reverse() as $m)
                            <option value="{{ $m['month_key'] }}" @selected($selectedMonth === $m['month_key'])>
                                {{ $m['month_label'] }} ({{ $m['total_orders'] }} order)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter_event" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">Program Event</label>
                    <select id="filter_event" name="event_id" class="input-heritage text-xs min-h-[38px] bg-[#FFFFFF]">
                        <option value="">Semua Event</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}" @selected($selectedEventId === $ev->id)>
                                {{ $ev->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter_status" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">Status Transaksi</label>
                    <select id="filter_status" name="status" class="input-heritage text-xs min-h-[38px] bg-[#FFFFFF]">
                        <option value="">Semua Status</option>
                        <option value="paid" @selected($selectedStatus === 'paid')>Lunas (Paid)</option>
                        <option value="pending" @selected($selectedStatus === 'pending')>Menunggu Pembayaran (Pending)</option>
                        <option value="expired" @selected($selectedStatus === 'expired')>Kedaluwarsa (Expired)</option>
                        <option value="cancelled" @selected($selectedStatus === 'cancelled')>Dibatalkan (Cancelled)</option>
                    </select>
                </div>

                <div>
                    <label for="filter_search" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">Cari Pemesan / Kode</label>
                    <input type="text"
                           id="filter_search"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Nama, email, atau GT-..."
                           class="input-heritage text-xs min-h-[38px] bg-[#FFFFFF]">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-heritage-primary text-xs py-2 px-4 min-h-[38px] cursor-pointer">
                        Filter Data
                    </button>
                    <a href="{{ route('admin.transactions.index') }}" class="text-xs text-[#6E635A] underline px-2">
                        Reset
                    </a>
                </div>
            </form>

            <!-- Detailed Transactions Table with Full Narrative Description -->
            <div class="overflow-x-auto border border-[#E3D9C9]">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                            <th class="py-3 px-4 font-semibold">Kode & Waktu</th>
                            <th class="py-3 px-4 font-semibold">Pemesan</th>
                            <th class="py-3 px-4 font-semibold">Event & Sesi</th>
                            <th class="py-3 px-4 font-semibold print:hidden">Deskripsi Lengkap Transaksi</th>
                            <th class="py-3 px-4 font-semibold text-right">Nominal & Metode</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3D9C9]">
                        @forelse($filteredOrders as $order)
                            <tr class="hover:bg-[#FAF6EE]/60 align-top">
                                <td class="py-4 px-4 tabular-nums whitespace-nowrap">
                                    <a href="{{ route('admin.orders.index', ['inspect' => $order->order_code]) }}"
                                       class="font-semibold text-[#6B3F1E] hover:underline block">
                                        {{ $order->order_code }}
                                    </a>
                                    <span class="text-xs text-[#6E635A] block mt-0.5">
                                        Order: {{ $order->created_at?->translatedFormat('d M Y, H:i') }}
                                    </span>
                                    @if($order->paid_at)
                                        <span class="text-[11px] text-[#4A6B3A] font-semibold block">
                                            Lunas: {{ $order->paid_at->translatedFormat('d M Y, H:i') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="py-4 px-4">
                                    <strong class="text-[#2A211A] block">{{ $order->buyer_name }}</strong>
                                    <span class="text-xs text-[#6E635A] block">{{ $order->buyer_email }}</span>
                                    <span class="text-xs text-[#6E635A] tabular-nums block">{{ $order->buyer_phone }}</span>
                                </td>

                                <td class="py-4 px-4">
                                    <strong class="text-xs text-[#2A211A] block leading-snug">
                                        {{ $order->schedule?->event?->title ?? '-' }}
                                    </strong>
                                    <span class="text-xs text-[#6B3F1E] font-semibold tabular-nums block mt-1">
                                        {{ $order->schedule?->tour_date?->translatedFormat('d M Y') }} &bull; Sesi {{ $order->schedule?->session_code }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-xs text-[#2A211A] leading-relaxed max-w-[46ch] print:hidden">
                                    {{ $order->transactionDescription() }}
                                </td>

                                <td class="py-4 px-4 text-right tabular-nums whitespace-nowrap">
                                    <strong class="font-serif text-base font-bold text-[#6B3F1E] block">
                                        {{ $order->formattedTotal() }}
                                    </strong>
                                    <span class="text-xs text-[#2A211A] font-medium block">
                                        {{ $order->ticket_qty }} Tiket &times; Rp {{ number_format($order->unit_price, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[11px] text-[#6E635A] block mt-0.5">
                                        {{ $order->paymentChannelLabel() }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-semibold border {{ $order->statusColorClass() }} inline-block">
                                        {{ $order->statusLabel() }}
                                    </span>
                                    <div class="mt-2 space-y-1 text-xs">
                                        <a href="{{ route('admin.orders.index', ['inspect' => $order->order_code]) }}"
                                           class="text-[#6B3F1E] hover:underline inline-flex items-center gap-1 font-medium group">
                                            <span>Kelola Order</span>
                                            <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        @if($order->status === 'paid')
                                            <a href="{{ route('orders.ticket', ['order_code' => $order->order_code, 'token' => $order->access_token]) }}"
                                               target="_blank"
                                               class="text-[#4A6B3A] hover:underline block font-medium">
                                                Lihat E-Ticket &nearr;
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-[#6E635A]">
                                    Tidak ditemukan data transaksi yang sesuai dengan filter periode/event di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- B. FORMAL DOCUMENT PRINT SHEET (Shown on Print / Print Preview Mode)  -->
    <!-- ===================================================================== -->
    <div id="formal-print-document" class="print-only-document bg-[#FFFFFF] text-[#1A1410] max-w-[1000px] mx-auto p-6 sm:p-10 print:p-0 border border-[#E3D9C9] print:border-0 space-y-6">

        <!-- Screen-Only Toolbar when inside Print Preview Mode -->
        <div class="no-print bg-[#F3EAD8] border border-[#6B3F1E] px-4 py-3 flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="text-xs text-[#2A211A]">
                <strong class="uppercase tracking-wider text-[#6B3F1E]">Mode Pratinjau Dokumen Resmi (A4):</strong>
                Tampilan ini adalah tata letak dokumen formal yang akan tercetak pada kertas / PDF.
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="btn-heritage-primary text-xs py-1.5 px-4 min-h-[34px] cursor-pointer">
                    Cetak / Simpan PDF Sekarang
                </button>
                <button type="button" id="close-print-preview-btn" class="btn-heritage-secondary text-xs py-1.5 px-3 min-h-[34px] cursor-pointer">
                    Kembali ke Tampilan Grafik
                </button>
            </div>
        </div>

        <!-- 1. KOP SURAT / HEADER DOKUMEN RESMI -->
        <header class="border-b-4 border-double border-[#1A1410] pb-4">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.12em] font-bold text-[#6B3F1E]">
                        Dokumen Laporan Operasional & Keuangan Resmi
                    </p>
                    <h1 class="font-serif text-2xl sm:text-3xl font-bold text-[#1A1410] tracking-tight mt-0.5">
                        PT PABRIK GULA CANDI BARU
                    </h1>
                    <p class="text-xs font-semibold text-[#2A211A] mt-0.5">
                        Unit Layanan Wisata Edukasi & Sejarah Industri Gula Nusantara (GulaTour — Berdiri 1911)
                    </p>
                    <p class="text-[11px] text-[#6E635A] mt-0.5">
                        Jl. Raya Candi No. 10, Kecamatan Candi, Kabupaten Sidoarjo, Jawa Timur 61271 &bull; Telp: (031) 8921002 &bull; Surel: info@pgcandibaru.co.id
                    </p>
                </div>
                <div class="text-right tabular-nums shrink-0 border border-[#4A3E35] px-3 py-2 bg-[#FAF6EE]">
                    <span class="text-[10px] uppercase tracking-wider text-[#6E635A] block">Nomor Dokumen</span>
                    <strong class="text-xs font-mono font-bold text-[#1A1410] block">{{ $docNumber }}</strong>
                    <span class="text-[10px] text-[#6E635A] block mt-1">Tanggal Cetak: {{ now()->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </header>

        <!-- 2. JUDUL DOKUMEN & METADATA LAPORAN -->
        <div class="text-center space-y-1 py-1">
            <h2 class="font-serif text-lg sm:text-xl font-bold uppercase tracking-wide text-[#1A1410] underline underline-offset-4">
                LAPORAN BULANAN TRANSAKSI, REKAPITULASI PEMESAN & POPULARITAS EVENT
            </h2>
            <p class="text-xs text-[#2A211A] tabular-nums">
                Periode Cakupan Data: <strong>{{ $reportPeriodLabel }}</strong>
                &bull; Status Filter: <strong>{{ $selectedStatus !== '' ? strtoupper($selectedStatus) : 'Semua Status Transaksi' }}</strong>
            </p>
        </div>

        <!-- BAGIAN I: RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY) -->
        <section class="report-doc-section space-y-2">
            <h3 class="font-serif text-sm font-bold uppercase tracking-wider text-[#1A1410]">
                I. Ringkasan Eksekutif (Executive Summary)
            </h3>
            <table class="report-doc-table tabular-nums">
                <thead>
                    <tr>
                        <th class="text-left w-1/4">Indikator Kinerja Utama</th>
                        <th class="text-left w-1/4">Nilai / Capaian</th>
                        <th class="text-left w-1/2">Keterangan & Analisis Ringkas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-semibold">Total Pendapatan Bersih (Lunas)</td>
                        <td class="font-bold text-[#6B3F1E]">Rp {{ number_format($grandTotalRevenue, 0, ',', '.') }}</td>
                        <td>Berhasil dihimpun dari <strong>{{ $paidOrders->count() }} transaksi lunas</strong> (Rata-rata Rp {{ number_format($avgOrderValue, 0, ',', '.') }} per transaksi).</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Total Tiket & Volume Order</td>
                        <td class="font-bold">{{ $grandTotalTickets }} Tiket ({{ $allOrders->count() }} Order)</td>
                        <td>Rincian: {{ $paidOrders->count() }} Lunas, {{ $pendingOrders->count() }} Menunggu Pembayaran (Rp {{ number_format((int) $pendingOrders->sum('total_amount'), 0, ',', '.') }}), {{ $voidOrders->count() }} Batal/Kedaluwarsa.</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Event Paling Sering Dipesan (#1)</td>
                        <td class="font-bold">{{ $topEvent['title'] ?? '-' }}</td>
                        <td>
                            @if($topEvent)
                                Dipesan sebanyak <strong>{{ $topEvent['total_orders'] }} kali</strong> ({{ $topEvent['tickets_sold'] }} tiket lunas) dengan kontribusi <strong>Rp {{ number_format($topEvent['revenue'], 0, ',', '.') }} ({{ $topEvent['revenue_share_pct'] }}%)</strong>.
                            @else
                                Belum ada data event.
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Pemesan Volume Tertinggi (#1)</td>
                        <td class="font-bold">{{ $topBuyer['buyer_name'] ?? '-' }}</td>
                        <td>
                            @if($topBuyer)
                                Total <strong>{{ $topBuyer['total_tickets_all'] }} tiket</strong> dari {{ $topBuyer['total_orders'] }} transaksi dengan nilai pembayaran lunas <strong>Rp {{ number_format($topBuyer['total_spent'], 0, ',', '.') }}</strong>.
                            @else
                                Belum ada data pemesan.
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- BAGIAN II: TABEL REKAPITULASI KINERJA BULANAN -->
        <section class="report-doc-section space-y-2">
            <h3 class="font-serif text-sm font-bold uppercase tracking-wider text-[#1A1410]">
                II. Rekapitulasi Transaksi & Pendapatan Bulanan (6 Bulan Terakhir)
            </h3>
            <table class="report-doc-table tabular-nums">
                <thead>
                    <tr>
                        <th class="text-center w-8">No</th>
                        <th class="text-left">Periode Bulan</th>
                        <th class="text-right">Jml Order</th>
                        <th class="text-center">Rincian Status (Lunas/Pending/Batal)</th>
                        <th class="text-right">Tiket Lunas</th>
                        <th class="text-right">Rasio Lunas</th>
                        <th class="text-left">Program Event Terlaris Bulan Ini</th>
                        <th class="text-right">Pendapatan Lunas (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyReport->reverse()->values() as $idx => $m)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="font-semibold">{{ $m['month_label'] }}</td>
                            <td class="text-right">{{ $m['total_orders'] }}</td>
                            <td class="text-center">{{ $m['paid_orders'] }} / {{ $m['pending_orders'] }} / {{ $m['void_orders'] }}</td>
                            <td class="text-right font-semibold">{{ $m['tickets_sold'] }} Tiket</td>
                            <td class="text-right">{{ $m['conversion_rate'] }}%</td>
                            <td>
                                @if($m['total_orders'] > 0)
                                    {{ $m['top_event_title'] }} ({{ $m['top_event_tickets'] }} tiket)
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right font-bold">Rp {{ number_format($m['revenue'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-left uppercase">Total Akumulasi</td>
                        <td class="text-right">{{ $allOrders->count() }}</td>
                        <td class="text-center">{{ $paidOrders->count() }} / {{ $pendingOrders->count() }} / {{ $voidOrders->count() }}</td>
                        <td class="text-right">{{ $grandTotalTickets }} Tiket</td>
                        <td class="text-right">
                            {{ $allOrders->count() > 0 ? round(($paidOrders->count() / $allOrders->count()) * 100) : 0 }}%
                        </td>
                        <td>Terpopuler: {{ $topEvent['title'] ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format($grandTotalRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <!-- BAGIAN III: PERINGKAT EVENT PALING SERING DIPESAN & KANAL PEMBAYARAN -->
        <section class="report-doc-section space-y-2">
            <h3 class="font-serif text-sm font-bold uppercase tracking-wider text-[#1A1410]">
                III. Peringkat Event Paling Sering Dipesan & Distribusi Kanal Pembayaran
            </h3>
            <table class="report-doc-table tabular-nums">
                <thead>
                    <tr>
                        <th class="text-center w-16">Peringkat</th>
                        <th class="text-left">Nama Program Event Tour</th>
                        <th class="text-right">Tarif Tetap</th>
                        <th class="text-right">Frekuensi Order</th>
                        <th class="text-right">Tiket Lunas</th>
                        <th class="text-right">Pemesan Unik</th>
                        <th class="text-right">Rata-rata/Order</th>
                        <th class="text-right">Pangsa Tiket</th>
                        <th class="text-right">Total Pendapatan (IDR)</th>
                        <th class="text-right">Pangsa (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eventRankings as $rankIndex => $ev)
                        <tr>
                            <td class="text-center font-bold">#{{ $rankIndex + 1 }}</td>
                            <td class="font-semibold">{{ $ev['title'] }}</td>
                            <td class="text-right">Rp {{ number_format($ev['ticket_price'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ $ev['total_orders'] }}x ({{ $ev['paid_orders'] }} Lunas)</td>
                            <td class="text-right font-bold">{{ $ev['tickets_sold'] }} Tiket</td>
                            <td class="text-right">{{ $ev['unique_buyers'] }} Orang</td>
                            <td class="text-right">{{ $ev['avg_tickets_per_order'] }} Tiket</td>
                            <td class="text-right">{{ $ev['ticket_share_pct'] }}%</td>
                            <td class="text-right font-bold">Rp {{ number_format($ev['revenue'], 0, ',', '.') }}</td>
                            <td class="text-right font-semibold">{{ $ev['revenue_share_pct'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <!-- BAGIAN IV: REKAPITULASI AKUMULASI TRANSAKSI TIAP PEMESAN -->
        <section class="report-doc-section space-y-2">
            <h3 class="font-serif text-sm font-bold uppercase tracking-wider text-[#1A1410]">
                IV. Rekapitulasi & Deskripsi Akumulasi Transaksi per Pemesan (10 Pemesan Tertinggi dari {{ $buyerSummaries->count() }})
            </h3>
            <table class="report-doc-table tabular-nums">
                <thead>
                    <tr>
                        <th class="text-center w-8">No</th>
                        <th class="text-left">Nama Pemesan</th>
                        <th class="text-left">Kontak (Email & WhatsApp)</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-right">Frekuensi</th>
                        <th class="text-right">Total Tiket</th>
                        <th class="text-right">Total Lunas (IDR)</th>
                        <th class="text-left">Deskripsi Aktivitas & Event yang Dipesan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($buyerSummaries->take(10) as $idx => $buyer)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="font-semibold">{{ $buyer['buyer_name'] }}</td>
                            <td>{{ $buyer['buyer_email'] }}<br>{{ $buyer['buyer_phone'] }}</td>
                            <td class="text-center">{{ $buyer['is_registered'] ? 'Akun' : 'Tamu' }}</td>
                            <td class="text-right">{{ $buyer['total_orders'] }}x</td>
                            <td class="text-right font-semibold">{{ $buyer['total_tickets_all'] }} Tiket</td>
                            <td class="text-right font-bold">Rp {{ number_format($buyer['total_spent'], 0, ',', '.') }}</td>
                            <td>{{ $buyer['description'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <!-- BAGIAN V: DAFTAR RINCIAN SETIAP TRANSAKSI -->
        <section class="report-doc-section space-y-2">
            <h3 class="font-serif text-sm font-bold uppercase tracking-wider text-[#1A1410]">
                V. Rincian Setiap Transaksi ({{ $filteredOrders->count() }} Transaksi)
            </h3>
            <table class="report-doc-table tabular-nums">
                <thead>
                    <tr>
                        <th class="text-center w-8">No</th>
                        <th class="text-left">Kode & Waktu Order</th>
                        <th class="text-left">Pemesan</th>
                        <th class="text-left">Program Event & Sesi</th>
                        <th class="text-right">Qty & Kanal</th>
                        <th class="text-right">Total (IDR)</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filteredOrders as $idx => $order)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="whitespace-nowrap">
                                <strong>{{ $order->order_code }}</strong><br>
                                <span class="text-[10px] text-[#6E635A]">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <strong>{{ $order->buyer_name }}</strong><br>
                                <span class="text-[10px] text-[#6E635A]">{{ $order->buyer_email }}</span>
                            </td>
                            <td>
                                <strong>{{ $order->schedule?->event?->title ?? '-' }}</strong><br>
                                <span class="text-[10px]">{{ $order->schedule?->tour_date?->format('Y-m-d') }} ({{ $order->schedule?->session_code }})</span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $order->ticket_qty }} Tiket<br>
                                <span class="text-[10px] text-[#6E635A]">{{ $order->paymentChannelLabel() }}</span>
                            </td>
                            <td class="text-right font-bold whitespace-nowrap">{{ $order->formattedTotal() }}</td>
                            <td class="text-center font-semibold uppercase text-[10px]">{{ $order->statusLabel() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <!-- 6. LEMBAR PENGESAHAN / TANDA TANGAN RESMI -->
        <div class="report-signature-block pt-6">
            <div class="grid grid-cols-2 gap-8 text-xs text-center">
                <div class="space-y-14">
                    <div>
                        <p class="text-[#6E635A]">Mengetahui & Menyetujui,</p>
                        <p class="font-bold text-[#1A1410] uppercase">Manajemen Operasional PT Pabrik Gula Candi Baru</p>
                    </div>
                    <div>
                        <p class="font-bold text-[#1A1410] underline underline-offset-4">( ........................................................ )</p>
                        <p class="text-[11px] text-[#6E635A] mt-0.5">Kepala Bagian Umum & Wisata Heritage</p>
                    </div>
                </div>

                <div class="space-y-14">
                    <div>
                        <p class="text-[#6E635A]">Sidoarjo, {{ now()->translatedFormat('d F Y') }}</p>
                        <p class="font-bold text-[#1A1410] uppercase">Disusun Oleh Petugas Administrasi GulaTour,</p>
                    </div>
                    <div>
                        <p class="font-bold text-[#1A1410] underline underline-offset-4">{{ auth()->user()?->name ?? 'Administrator PG Candi Baru' }}</p>
                        <p class="text-[11px] text-[#6E635A] mt-0.5">Admin Operasional & Ticketing GulaTour</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================================================================== -->
    <!-- C. MODAL POP-UP PEMILIHAN PERIODE CETAK LAPORAN                       -->
    <!-- ===================================================================== -->
    <div id="print-selection-modal"
         role="dialog"
         aria-modal="true"
         aria-labelledby="print-modal-title"
         class="hidden fixed inset-0 z-50 bg-[#2A211A]/80 backdrop-blur-sm flex items-center justify-center p-4 no-print">
        <div class="bg-[#FAF6EE] border-2 border-[#6B3F1E] shadow-2xl max-w-md w-full overflow-hidden transition-all transform animate-in fade-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="bg-[#6B3F1E] text-[#FAF6EE] px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base font-serif font-bold text-[#C8A24A]">✦</span>
                    <h3 id="print-modal-title" class="font-serif text-base font-bold tracking-wide">
                        Pilih Periode Cetak Laporan
                    </h3>
                </div>
                <button type="button"
                        id="close-print-modal-btn"
                        class="text-[#FAF6EE]/80 hover:text-[#FAF6EE] text-xl font-bold leading-none cursor-pointer p-1">
                    &times;
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5 bg-[#FFFFFF]">
                <p class="text-xs text-[#6E635A] leading-relaxed">
                    Tentukan cakupan data transaksi yang akan dimuat ke dalam dokumen formal cetak / PDF PT Pabrik Gula Candi Baru.
                </p>

                <div class="space-y-3">
                    <!-- Option 1: Semua Data / Semua Bulan -->
                    <label class="flex items-start gap-3 p-3.5 border border-[#E3D9C9] bg-[#FAF6EE]/60 hover:bg-[#F3EAD8] cursor-pointer transition-colors group">
                        <input type="radio"
                               name="print_scope_option"
                               value="all"
                               class="mt-1 text-[#6B3F1E] focus:ring-[#6B3F1E] cursor-pointer"
                               @checked($selectedMonth === '')>
                        <div>
                            <span class="text-sm font-semibold text-[#2A211A] group-hover:text-[#6B3F1E] block">
                                Cetak Semua Data Transaksi
                            </span>
                            <span class="text-xs text-[#6E635A] mt-0.5 block">
                                Mencakup seluruh rekapitulasi data 6 bulan terakhir (semua periode).
                            </span>
                        </div>
                    </label>

                    <!-- Option 2: Bulan Tertentu -->
                    <label class="flex items-start gap-3 p-3.5 border border-[#E3D9C9] bg-[#FAF6EE]/60 hover:bg-[#F3EAD8] cursor-pointer transition-colors group">
                        <input type="radio"
                               name="print_scope_option"
                               value="month"
                               class="mt-1 text-[#6B3F1E] focus:ring-[#6B3F1E] cursor-pointer"
                               @checked($selectedMonth !== '')>
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-[#2A211A] group-hover:text-[#6B3F1E] block">
                                Cetak Hanya Bulan Tertentu
                            </span>
                            <span class="text-xs text-[#6E635A] mt-0.5 block">
                                Laporan akan disaring khusus untuk transaksi pada bulan yang Anda pilih.
                            </span>

                            <!-- Dropdown Pilihan Bulan -->
                            <div id="print-month-selector-wrapper" class="mt-3 {{ $selectedMonth !== '' ? '' : 'hidden' }}">
                                <label for="print_target_month" class="block text-[11px] font-semibold uppercase tracking-wider text-[#6B3F1E] mb-1">
                                    Pilih Bulan Laporan:
                                </label>
                                <select id="print_target_month" class="input-heritage text-xs min-h-[38px] bg-[#FFFFFF] w-full">
                                    @foreach($monthlyReport->reverse() as $m)
                                        <option value="{{ $m['month_key'] }}" @selected($selectedMonth === $m['month_key'])>
                                            {{ $m['month_label'] }} ({{ $m['total_orders'] }} order &bull; Rp {{ number_format($m['revenue'], 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-[#FAF6EE] border-t border-[#E3D9C9] flex items-center justify-end gap-3">
                <button type="button"
                        id="cancel-print-modal-btn"
                        class="btn-heritage-secondary text-xs py-2 px-4 cursor-pointer">
                    Batal
                </button>
                <button type="button"
                        id="confirm-print-btn"
                        class="btn-heritage-primary text-xs py-2 px-5 cursor-pointer inline-flex items-center gap-1.5 group">
                    <span>Lanjutkan Cetak (PDF)</span>
                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-print-preview-btn');
            const closePreviewBtn = document.getElementById('close-print-preview-btn');
            const dashboardView = document.getElementById('interactive-report-dashboard');
            const formalDoc = document.getElementById('formal-print-document');

            // Modal elements
            const openModalBtn = document.getElementById('open-print-dialog-btn');
            const printModal = document.getElementById('print-selection-modal');
            const closeModalBtn = document.getElementById('close-print-modal-btn');
            const cancelModalBtn = document.getElementById('cancel-print-modal-btn');
            const confirmPrintBtn = document.getElementById('confirm-print-btn');
            const scopeRadios = document.querySelectorAll('input[name="print_scope_option"]');
            const monthSelectorWrapper = document.getElementById('print-month-selector-wrapper');
            const targetMonthSelect = document.getElementById('print_target_month');

            const currentSelectedMonth = @json($selectedMonth);
            const currentSelectedStatus = @json($selectedStatus);
            const currentSelectedEventId = @json($selectedEventId);
            const currentSearch = @json($search);

            function openModal() {
                if (printModal) {
                    printModal.classList.remove('hidden');
                }
            }

            function closeModal() {
                if (printModal) {
                    printModal.classList.add('hidden');
                }
            }

            if (openModalBtn) {
                openModalBtn.addEventListener('click', openModal);
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeModal);
            }

            if (cancelModalBtn) {
                cancelModalBtn.addEventListener('click', closeModal);
            }

            // Close on backdrop click
            if (printModal) {
                printModal.addEventListener('click', function (e) {
                    if (e.target === printModal) {
                        closeModal();
                    }
                });
            }

            // Toggle dropdown bulan sesuai opsi radio
            scopeRadios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    if (this.value === 'month') {
                        monthSelectorWrapper.classList.remove('hidden');
                    } else {
                        monthSelectorWrapper.classList.add('hidden');
                    }
                });
            });

            // Eksekusi cetak / redirect parameter bulan
            if (confirmPrintBtn) {
                confirmPrintBtn.addEventListener('click', function () {
                    const selectedScope = document.querySelector('input[name="print_scope_option"]:checked')?.value || 'all';
                    let desiredMonth = '';

                    if (selectedScope === 'month' && targetMonthSelect) {
                        desiredMonth = targetMonthSelect.value;
                    }

                    closeModal();

                    // Jika periode yang dipilih sama persis dengan yang sedang aktif di halaman saat ini
                    if (desiredMonth === currentSelectedMonth) {
                        window.print();
                    } else {
                        // Redirect ke URL dengan parameter month yang diinginkan dan trigger print
                        const url = new URL(window.location.origin + window.location.pathname);
                        if (desiredMonth) {
                            url.searchParams.set('month', desiredMonth);
                        }
                        if (currentSelectedStatus) {
                            url.searchParams.set('status', currentSelectedStatus);
                        }
                        if (currentSelectedEventId) {
                            url.searchParams.set('event_id', currentSelectedEventId);
                        }
                        if (currentSearch) {
                            url.searchParams.set('search', currentSearch);
                        }
                        url.searchParams.set('auto_print', '1');
                        window.location.href = url.toString();
                    }
                });
            }

            // Cek jika diarahkan dari pilihan modal dengan auto_print
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1') {
                // Hapus query param auto_print dari URL tanpa reload agar rapi
                urlParams.delete('auto_print');
                const cleanQuery = urlParams.toString() ? '?' + urlParams.toString() : '';
                window.history.replaceState({}, document.title, window.location.pathname + cleanQuery + window.location.hash);

                // Sedikit delay agar layout browser siap sebelum membuka dialog cetak
                setTimeout(function () {
                    window.print();
                }, 300);
            }

            function setPreviewMode(isPreview) {
                if (!dashboardView || !formalDoc) return;
                if (isPreview) {
                    dashboardView.classList.add('hidden');
                    formalDoc.classList.add('print-preview-active');
                    if (toggleBtn) {
                        toggleBtn.textContent = 'Kembali ke Mode Grafik';
                    }
                } else {
                    dashboardView.classList.remove('hidden');
                    formalDoc.classList.remove('print-preview-active');
                    if (toggleBtn) {
                        toggleBtn.textContent = 'Pratinjau Dokumen Cetak';
                    }
                }
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    const isCurrentlyPreview = formalDoc && formalDoc.classList.contains('print-preview-active');
                    setPreviewMode(!isCurrentlyPreview);
                });
            }

            if (closePreviewBtn) {
                closePreviewBtn.addEventListener('click', function () {
                    setPreviewMode(false);
                });
            }
        });
    </script>
@endsection
