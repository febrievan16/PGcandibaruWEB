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
        $avgOrderValue = $paidOrders->count() > 0 ? (int) round($grandTotalRevenue / $paidOrders->count()) : 0;

        $topEvent = $eventRankings->first();
        $topBuyer = $buyerSummaries->first();

        $reportPeriodLabel = $selectedMonth !== ''
            ? \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $selectedMonth . '-01')->translatedFormat('F Y')
            : 'Rekapitulasi 6 Bulan Terakhir (Semua Periode)';
        $docNumber = 'LAP-GT/' . now()->format('Y/m') . '/' . str_pad((string) $allOrders->count(), 3, '0', STR_PAD_LEFT);
    @endphp

    <!-- ===================================================================== -->
    <!-- A. INTERACTIVE WEB DASHBOARD VIEW (Hidden automatically during print) -->
    <!-- ===================================================================== -->
    <div id="interactive-report-dashboard" class="space-y-8 no-print">

        <!-- 1. Executive KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5">
                <span class="text-xs uppercase tracking-wider text-[#6E635A] font-semibold block">Total Pendapatan Lunas</span>
                <span class="font-serif text-2xl font-bold text-[#6B3F1E] tabular-nums block mt-1">
                    Rp {{ number_format($grandTotalRevenue, 0, ',', '.') }}
                </span>
                <span class="text-xs text-[#4A6B3A] mt-1.5 block tabular-nums">
                    {{ $paidOrders->count() }} transaksi lunas &bull; Rata-rata Rp {{ number_format($avgOrderValue, 0, ',', '.') }}/order
                </span>
            </div>

            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5">
                <span class="text-xs uppercase tracking-wider text-[#6E635A] font-semibold block">Total Tiket & Peserta</span>
                <span class="font-serif text-2xl font-bold text-[#2A211A] tabular-nums block mt-1">
                    {{ $grandTotalTickets }} Tiket Lunas
                </span>
                <span class="text-xs text-[#6E635A] mt-1.5 block tabular-nums">
                    Total {{ $allOrders->count() }} order ({{ $pendingOrders->count() }} pending, {{ $voidOrders->count() }} batal/expired)
                </span>
            </div>

            <div class="border border-[#6B3F1E] bg-[#F3EAD8]/65 p-5">
                <span class="text-xs uppercase tracking-wider text-[#6B3F1E] font-bold block">Event Paling Sering Dipesan (#1)</span>
                <span class="font-serif text-lg font-bold text-[#2A211A] block mt-1 leading-snug">
                    {{ $topEvent['title'] ?? '-' }}
                </span>
                @if($topEvent)
                    <span class="text-xs text-[#6B3F1E] font-semibold mt-1.5 block tabular-nums">
                        {{ $topEvent['total_orders'] }}x dipesan &bull; {{ $topEvent['tickets_sold'] }} tiket ({{ $topEvent['revenue_share_pct'] }}% pendapatan)
                    </span>
                @endif
            </div>

            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-5">
                <span class="text-xs uppercase tracking-wider text-[#A67C52] font-semibold block">Pemesan Volume Tertinggi (#1)</span>
                <span class="font-serif text-lg font-bold text-[#2A211A] block mt-1 leading-snug">
                    {{ $topBuyer['buyer_name'] ?? '-' }}
                </span>
                @if($topBuyer)
                    <span class="text-xs text-[#4A6B3A] font-semibold mt-1.5 block tabular-nums">
                        {{ $topBuyer['total_tickets_all'] }} tiket ({{ $topBuyer['total_orders'] }}x order) &bull; Rp {{ number_format($topBuyer['total_spent'], 0, ',', '.') }}
                    </span>
                @endif
            </div>
        </div>

        <!-- 2. Visualisasi Laporan Bulanan (Grafik Visual & Tabel Rincian Per Bulan) -->
        <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-[#E3D9C9] pb-4">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                        Visualisasi Data Laporan Bulanan
                    </span>
                    <h2 class="font-serif text-2xl font-bold text-[#2A211A] mt-0.5">
                        Grafik Tren Pendapatan & Penjualan Tiket Bulanan (6 Bulan Terakhir)
                    </h2>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-xs">
                    <span class="inline-flex items-center gap-1.5 font-semibold text-[#2A211A]">
                        <span class="w-3.5 h-3.5 bg-[#6B3F1E] inline-block border border-[#2A211A]"></span>
                        Pendapatan Lunas (IDR)
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-semibold text-[#2A211A]">
                        <span class="w-3.5 h-3.5 bg-[#C8A24A] inline-block border border-[#6B3F1E]"></span>
                        Jumlah Tiket Terjual
                    </span>
                </div>
            </div>

            <!-- Interactive Visual Bar Chart (Monthly Revenue & Tickets) -->
            <div class="bg-[#FAF6EE] border border-[#E3D9C9] p-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 items-end min-h-[250px] pt-8">
                    @foreach($monthlyReport as $m)
                        @php
                            $revHeightPct = $m['revenue'] > 0
                                ? max(12, (int) round(($m['revenue'] / $maxMonthlyRevenue) * 100))
                                : 4;
                            $tktHeightPct = $m['tickets_sold'] > 0
                                ? max(10, (int) round(($m['tickets_sold'] / $maxMonthlyTickets) * 100))
                                : 4;
                            $isSelectedMonth = ($selectedMonth === $m['month_key']);
                        @endphp
                        <a href="{{ route('admin.transactions.index', ['month' => $isSelectedMonth ? '' : $m['month_key']]) }}#tabel-rincian-transaksi"
                           title="Klik untuk memfilter transaksi bulan {{ $m['month_label'] }}"
                           class="group flex flex-col justify-end h-full p-3 border transition-colors {{ $isSelectedMonth ? 'border-[#6B3F1E] bg-[#F3EAD8]' : 'border-[#E3D9C9] bg-[#FFFFFF] hover:border-[#6B3F1E]' }}">

                            <div class="text-center mb-3 space-y-0.5 tabular-nums">
                                <span class="text-[11px] font-bold text-[#6B3F1E] block">
                                    Rp {{ number_format($m['revenue'], 0, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-[#6E635A] block">
                                    {{ $m['tickets_sold'] }} tiket &bull; {{ $m['total_orders'] }} order
                                </span>
                            </div>

                            <div class="h-36 flex items-end justify-center gap-2.5 border-b-2 border-[#2A211A] pb-1 px-2">
                                <div class="w-7 bg-[#6B3F1E] group-hover:bg-[#563218] transition-all relative"
                                     style="height: {{ $revHeightPct }}%"
                                     aria-label="Pendapatan {{ $m['month_label'] }}: Rp {{ number_format($m['revenue'], 0, ',', '.') }}">
                                </div>
                                <div class="w-7 bg-[#C8A24A] border border-[#6B3F1E] transition-all relative"
                                     style="height: {{ $tktHeightPct }}%"
                                     aria-label="Tiket Terjual {{ $m['month_label'] }}: {{ $m['tickets_sold'] }} tiket">
                                </div>
                            </div>

                            <div class="pt-2.5 text-center">
                                <strong class="text-xs font-serif font-bold text-[#2A211A] block">{{ $m['month_label'] }}</strong>
                                <span class="text-[10px] uppercase tracking-wider {{ $m['conversion_rate'] >= 70 ? 'text-[#4A6B3A]' : 'text-[#6E635A]' }} font-semibold">
                                    Konversi {{ $m['conversion_rate'] }}%
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Detailed Monthly Breakdown Table -->
            <div class="overflow-x-auto border border-[#E3D9C9]">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                            <th class="py-3 px-4 font-semibold">Periode Bulan</th>
                            <th class="py-3 px-4 font-semibold text-right">Total Order</th>
                            <th class="py-3 px-4 font-semibold text-right">Rincian Status</th>
                            <th class="py-3 px-4 font-semibold text-right">Tiket Terjual (Lunas)</th>
                            <th class="py-3 px-4 font-semibold">Event Paling Diminati Bulan Ini</th>
                            <th class="py-3 px-4 font-semibold text-right">Tagihan Pending</th>
                            <th class="py-3 px-4 font-semibold text-right">Pendapatan Bersih (Lunas)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3D9C9]">
                        @foreach($monthlyReport->reverse() as $m)
                            <tr class="hover:bg-[#FAF6EE]/60 {{ $selectedMonth === $m['month_key'] ? 'bg-[#F3EAD8]/60' : '' }}">
                                <td class="py-3.5 px-4 font-serif font-bold text-[#2A211A]">
                                    <a href="{{ route('admin.transactions.index', ['month' => $m['month_key']]) }}#tabel-rincian-transaksi"
                                       class="hover:text-[#6B3F1E] underline-offset-4 hover:underline">
                                        {{ $m['month_label'] }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums font-semibold">
                                    {{ $m['total_orders'] }} Transaksi
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums text-xs">
                                    <span class="text-[#4A6B3A] font-semibold">{{ $m['paid_orders'] }} Lunas</span> &bull;
                                    <span class="text-[#B8860B] font-semibold">{{ $m['pending_orders'] }} Pending</span> &bull;
                                    <span class="text-[#8B2E1F]">{{ $m['void_orders'] }} Batal</span>
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums font-bold text-[#2A211A]">
                                    {{ $m['tickets_sold'] }} Tiket
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if($m['total_orders'] > 0)
                                        <strong class="text-[#2A211A]">{{ $m['top_event_title'] }}</strong>
                                        <span class="text-[#6E635A] tabular-nums">({{ $m['top_event_tickets'] }} tiket)</span>
                                    @else
                                        <span class="text-[#6E635A] italic">Belum ada transaksi</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums text-[#B8860B] font-medium">
                                    Rp {{ number_format($m['pending_amount'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums font-serif font-bold text-base text-[#6B3F1E]">
                                    Rp {{ number_format($m['revenue'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-[#6B3F1E] bg-[#F3EAD8] font-bold text-[#2A211A]">
                            <td class="py-3.5 px-4 uppercase text-xs tracking-wider">Total Akumulasi Laporan</td>
                            <td class="py-3.5 px-4 text-right tabular-nums">{{ $allOrders->count() }} Transaksi</td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-xs">
                                {{ $paidOrders->count() }} Lunas / {{ $pendingOrders->count() }} Pending / {{ $voidOrders->count() }} Batal
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums">{{ $grandTotalTickets }} Tiket</td>
                            <td class="py-3.5 px-4 text-xs text-[#6B3F1E]">
                                Terpopuler: {{ $topEvent['title'] ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-[#B8860B]">
                                Rp {{ number_format((int) $pendingOrders->sum('total_amount'), 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums font-serif text-lg text-[#6B3F1E]">
                                Rp {{ number_format($grandTotalRevenue, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 3. Peringkat Event Paling Sering Dipesan & Distribusi Metode Pembayaran -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left 7/12: Most Frequently Ordered Events Visual Analytics -->
            <div class="lg:col-span-7 border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-5">
                <div class="border-b border-[#E3D9C9] pb-4 flex items-center justify-between gap-2">
                    <div>
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                            Analisis Popularitas Program Tour
                        </span>
                        <h2 class="font-serif text-xl font-bold text-[#2A211A] mt-0.5">
                            Peringkat Event Paling Sering Dipesan
                        </h2>
                    </div>
                    <span class="text-xs text-[#6E635A]">Diurutkan berdasarkan volume tiket & order</span>
                </div>

                <div class="space-y-5">
                    @foreach($eventRankings as $rankIndex => $ev)
                        <div class="border {{ $rankIndex === 0 ? 'border-[#6B3F1E] bg-[#F3EAD8]/40' : 'border-[#E3D9C9] bg-[#FAF6EE]' }} p-4 space-y-3">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider {{ $rankIndex === 0 ? 'bg-[#6B3F1E] text-[#FAF6EE]' : 'bg-[#E3D9C9] text-[#2A211A]' }}">
                                            Peringkat #{{ $rankIndex + 1 }} {{ $rankIndex === 0 ? '— Paling Sering Dipesan' : '' }}
                                        </span>
                                        <span class="text-xs text-[#6E635A] tabular-nums">
                                            Tarif Tetap: Rp {{ number_format($ev['ticket_price'], 0, ',', '.') }}/tiket
                                        </span>
                                    </div>
                                    <h3 class="font-serif text-lg font-bold text-[#2A211A]">
                                        <a href="{{ route('admin.transactions.index', ['event_id' => $ev['event_id']]) }}#tabel-rincian-transaksi" class="hover:text-[#6B3F1E]">
                                            {{ $ev['title'] }}
                                        </a>
                                    </h3>
                                </div>

                                <div class="text-right tabular-nums">
                                    <span class="font-serif text-xl font-bold text-[#6B3F1E] block">
                                        Rp {{ number_format($ev['revenue'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-[#6E635A]">
                                        {{ $ev['revenue_share_pct'] }}% dari total pendapatan
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-[#E3D9C9] text-xs tabular-nums">
                                <div>
                                    <span class="text-[#6E635A] block">Frekuensi Order</span>
                                    <strong class="text-sm text-[#2A211A]">{{ $ev['total_orders'] }}x Dipesan ({{ $ev['paid_orders'] }} Lunas)</strong>
                                </div>
                                <div>
                                    <span class="text-[#6E635A] block">Tiket Terjual</span>
                                    <strong class="text-sm text-[#4A6B3A]">{{ $ev['tickets_sold'] }} Tiket Lunas</strong>
                                </div>
                                <div>
                                    <span class="text-[#6E635A] block">Pemesan Unik</span>
                                    <strong class="text-sm text-[#2A211A]">{{ $ev['unique_buyers'] }} Pemesan</strong>
                                </div>
                                <div>
                                    <span class="text-[#6E635A] block">Rata-rata Rombongan</span>
                                    <strong class="text-sm text-[#2A211A]">{{ $ev['avg_tickets_per_order'] }} Tiket / Order</strong>
                                </div>
                            </div>

                            <div class="space-y-2 pt-1 text-xs tabular-nums">
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-[#6E635A]">Pangsa Penjualan Tiket ({{ $ev['tickets_sold'] }} tiket)</span>
                                        <strong class="text-[#2A211A]">{{ $ev['ticket_share_pct'] }}%</strong>
                                    </div>
                                    <div class="w-full h-2.5 bg-[#FFFFFF] border border-[#E3D9C9]">
                                        <div class="h-full bg-[#6B3F1E]" style="width: {{ min(100, $ev['ticket_share_pct']) }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-[#6E635A]">Pangsa Frekuensi Transaksi ({{ $ev['total_orders'] }} order)</span>
                                        <strong class="text-[#2A211A]">{{ $ev['order_share_pct'] }}%</strong>
                                    </div>
                                    <div class="w-full h-2 bg-[#FFFFFF] border border-[#E3D9C9]">
                                        <div class="h-full bg-[#C8A24A]" style="width: {{ min(100, $ev['order_share_pct']) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right 5/12: Payment Channel Distribution & Buyer Segment Summary -->
            <div class="lg:col-span-5 space-y-6">
                <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4">
                    <div class="border-b border-[#E3D9C9] pb-3">
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                            Kanal Pembayaran Midtrans
                        </span>
                        <h2 class="font-serif text-xl font-bold text-[#2A211A] mt-0.5">
                            Distribusi Metode Pembayaran Lunas
                        </h2>
                    </div>

                    <div class="space-y-4">
                        @forelse($paymentChannelStats as $stat)
                            <div class="space-y-1.5 text-xs tabular-nums">
                                <div class="flex items-center justify-between">
                                    <strong class="text-sm text-[#2A211A]">{{ $stat['channel'] }}</strong>
                                    <span class="font-bold text-[#6B3F1E]">
                                        Rp {{ number_format($stat['revenue'], 0, ',', '.') }} ({{ $stat['share_pct'] }}%)
                                    </span>
                                </div>
                                <div class="w-full h-2.5 bg-[#FAF6EE] border border-[#E3D9C9]">
                                    <div class="h-full bg-[#4A6B3A]" style="width: {{ min(100, max(6, $stat['share_pct'])) }}%"></div>
                                </div>
                                <div class="text-[#6E635A] flex justify-between">
                                    <span>{{ $stat['orders_count'] }} transaksi lunas</span>
                                    <span>{{ $stat['tickets_count'] }} tiket diterbitkan</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-[#6E635A]">Belum ada data kanal pembayaran lunas.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Summary of Top Buyers Card -->
                <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4">
                    <div class="border-b border-[#E3D9C9] pb-3">
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                            Loyalitas & Segmen Pengunjung
                        </span>
                        <h2 class="font-serif text-xl font-bold text-[#2A211A] mt-0.5">
                            5 Pemesan dengan Kontribusi Tertinggi
                        </h2>
                    </div>

                    <div class="divide-y divide-[#E3D9C9]">
                        @foreach($buyerSummaries->take(5) as $idx => $buyer)
                            <div class="py-3 flex items-start justify-between gap-3 text-xs">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-serif font-bold text-[#6B3F1E]">#{{ $idx + 1 }}</span>
                                        <strong class="text-sm text-[#2A211A]">{{ $buyer['buyer_name'] }}</strong>
                                    </div>
                                    <span class="text-[#6E635A] block">{{ $buyer['buyer_email'] }} &bull; {{ $buyer['is_registered'] ? 'Akun Terdaftar' : 'Tamu' }}</span>
                                </div>
                                <div class="text-right tabular-nums shrink-0">
                                    <strong class="text-sm text-[#6B3F1E] block">Rp {{ number_format($buyer['total_spent'], 0, ',', '.') }}</strong>
                                    <span class="text-[#4A6B3A] font-semibold">{{ $buyer['total_tickets_all'] }} tiket ({{ $buyer['total_orders'] }}x order)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Ringkasan Deskripsi Aktivitas Transaksi per Pemesan -->
        <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4">
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
        <div id="tabel-rincian-transaksi" class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-5">
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
                                           class="text-[#6B3F1E] hover:underline block font-medium">
                                            Kelola Order &rarr;
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
                        class="btn-heritage-primary text-xs py-2 px-5 cursor-pointer flex items-center gap-1.5">
                    <span>Lanjutkan Cetak (PDF)</span>
                    <span>&rarr;</span>
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
