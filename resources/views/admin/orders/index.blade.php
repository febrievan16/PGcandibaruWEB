@extends('layouts.admin')

@section('title', 'Daftar Pemesanan (Master-Detail) — Admin GulaTour')
@section('page_title', 'Daftar Pemesanan & Ekspor Laporan')
@section('page_subtitle', 'Pola Master-Detail (DESIGN.md §6): Pilih baris pesanan di sebelah kiri untuk memeriksa detail dan memperbarui status di panel kanan.')

@section('header_actions')
    <a href="{{ route('api.v1.admin.orders', array_merge(request()->only(['status', 'schedule_id']), ['export' => 'csv'])) }}"
       class="btn-heritage-primary text-xs py-2 px-4">
        Ekspor CSV Sesuai Filter (FR-15)
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Filter Bar -->
        <form action="{{ route('admin.orders.index') }}" method="GET" class="border border-[#E3D9C9] bg-[#FFFFFF] p-4 grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">Cari Kode / Nama / Email</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="GT-... atau nama" class="input-heritage text-sm min-h-[38px]">
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">Status Pembayaran</label>
                <select id="status" name="status" class="input-heritage text-sm min-h-[38px]">
                    <option value="">Semua Status</option>
                    <option value="paid" @selected(request('status') === 'paid')>Lunas (paid)</option>
                    <option value="pending" @selected(request('status') === 'pending')>Menunggu Pembayaran (pending)</option>
                    <option value="expired" @selected(request('status') === 'expired')>Kedaluwarsa (expired)</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan (cancelled)</option>
                </select>
            </div>

            <div>
                <label for="schedule_id" class="block text-xs font-semibold uppercase tracking-wider text-[#6E635A] mb-1">Jadwal Sesi</label>
                <select id="schedule_id" name="schedule_id" class="input-heritage text-sm min-h-[38px]">
                    <option value="">Semua Sesi</option>
                    @foreach($schedules as $sch)
                        <option value="{{ $sch->id }}" @selected((int) request('schedule_id') === $sch->id)>
                            {{ $sch->tour_date->format('Y-m-d') }} ({{ $sch->session_code }}) — {{ $sch->event->title ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn-heritage-secondary text-xs py-2 px-4 min-h-[38px]">Terapkan Filter</button>
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-[#6E635A] underline px-2">Reset</a>
            </div>
        </form>

        <!-- Master-Detail Layout (List Kiri 7/12, Detail Kanan 5/12 on Desktop per DESIGN.md §6 & §10) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Master Table (Left) -->
            <div class="lg:col-span-7 border border-[#E3D9C9] bg-[#FFFFFF] overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                            <th class="py-3 px-3.5 font-semibold">Kode Order</th>
                            <th class="py-3 px-3.5 font-semibold">Pemesan</th>
                            <th class="py-3 px-3.5 font-semibold">Jadwal</th>
                            <th class="py-3 px-3.5 font-semibold text-right">Qty</th>
                            <th class="py-3 px-3.5 font-semibold text-right">Total</th>
                            <th class="py-3 px-3.5 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3D9C9]">
                        @forelse($orders as $item)
                            @php
                                $isCurrent = ($selectedOrder && $selectedOrder->id === $item->id);
                            @endphp
                            <tr class="{{ $isCurrent ? 'bg-[#F3EAD8]' : 'hover:bg-[#FAF6EE]/60' }}">
                                <td class="py-3 px-3.5 font-semibold tabular-nums">
                                    <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['inspect' => $item->order_code])) }}"
                                       class="text-[#6B3F1E] hover:underline block">
                                        {{ $item->order_code }}
                                    </a>
                                </td>
                                <td class="py-3 px-3.5">
                                    <div class="font-medium text-[#2A211A]">{{ $item->buyer_name }}</div>
                                    <div class="text-xs text-[#6E635A]">{{ $item->buyer_email }}</div>
                                </td>
                                <td class="py-3 px-3.5 text-xs tabular-nums text-[#6E635A]">
                                    {{ $item->schedule->tour_date->format('Y-m-d') }}<br>
                                    {{ $item->schedule->session_code }}
                                </td>
                                <td class="py-3 px-3.5 text-right tabular-nums">{{ $item->ticket_qty }}</td>
                                <td class="py-3 px-3.5 text-right tabular-nums font-semibold">{{ $item->formattedTotal() }}</td>
                                <td class="py-3 px-3.5">
                                    <span class="px-2 py-0.5 text-[11px] font-semibold border {{ $item->statusColorClass() }}">
                                        {{ $item->statusLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-[#6E635A]">Tidak ada data pemesanan yang sesuai dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($orders->hasPages())
                    <div class="p-3 border-t border-[#E3D9C9] bg-[#FAF6EE]">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>

            <!-- Detail Pane (Right) -->
            <div class="lg:col-span-5 border border-[#E3D9C9] bg-[#FFFFFF] p-5 space-y-5">
                @if(!$selectedOrder)
                    <p class="text-sm text-[#6E635A] text-center py-8">Pilih salah satu pesanan pada tabel untuk melihat detail lengkap.</p>
                @else
                    <div class="border-b border-[#E3D9C9] pb-4 flex items-start justify-between gap-3">
                        <div>
                            <span class="text-xs uppercase tracking-wider text-[#A67C52] font-semibold block">Detail Pesanan Terpilih</span>
                            <h2 class="font-serif text-xl font-bold text-[#2A211A] tabular-nums">{{ $selectedOrder->order_code }}</h2>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold border {{ $selectedOrder->statusColorClass() }}">
                            {{ $selectedOrder->statusLabel() }}
                        </span>
                    </div>

                    <dl class="divide-y divide-[#E3D9C9] text-xs border-b border-[#E3D9C9]">
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-[#6E635A]">Nama Pemesan</dt>
                            <dd class="font-semibold text-[#2A211A]">{{ $selectedOrder->buyer_name }} ({{ $selectedOrder->user_id ? 'Akun Terdaftar' : 'Tamu / Guest' }})</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-[#6E635A]">Kontak</dt>
                            <dd class="text-[#2A211A] tabular-nums">{{ $selectedOrder->buyer_email }} / {{ $selectedOrder->buyer_phone }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-[#6E635A]">Event</dt>
                            <dd class="font-medium text-[#2A211A]">{{ $selectedOrder->schedule->event->title ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-[#6E635A]">Jadwal & Sesi</dt>
                            <dd class="tabular-nums text-[#2A211A]">{{ $selectedOrder->schedule->tour_date->format('Y-m-d') }} ({{ $selectedOrder->schedule->formattedTimeRange() }})</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-[#6E635A]">Total Pembayaran</dt>
                            <dd class="font-bold text-[#6B3F1E] tabular-nums">{{ $selectedOrder->ticket_qty }} Tiket &bull; {{ $selectedOrder->formattedTotal() }}</dd>
                        </div>
                    </dl>

                    <!-- Manual Status Update (FR-14) -->
                    <form action="{{ route('admin.orders.status', $selectedOrder) }}" method="POST" class="space-y-3 bg-[#FAF6EE] p-4 border border-[#E3D9C9]">
                        @csrf
                        <label for="manual_status" class="block text-xs font-semibold uppercase tracking-wider text-[#2A211A]">
                            Perbarui Status Manual (FR-14):
                        </label>
                        <div class="flex items-center gap-2">
                            <select id="manual_status" name="status" class="input-heritage text-xs min-h-[38px]">
                                <option value="pending" @selected($selectedOrder->status === 'pending')>Menunggu Pembayaran (pending)</option>
                                <option value="paid" @selected($selectedOrder->status === 'paid')>Lunas & Terbitkan E-Ticket (paid)</option>
                                <option value="expired" @selected($selectedOrder->status === 'expired')>Kedaluwarsa & Kembalikan Kuota (expired)</option>
                                <option value="cancelled" @selected($selectedOrder->status === 'cancelled')>Dibatalkan & Kembalikan Kuota (cancelled)</option>
                            </select>
                            <button type="submit" class="btn-heritage-secondary text-xs py-2 px-3 min-h-[38px] shrink-0">
                                Simpan Status
                            </button>
                        </div>
                    </form>

                    <!-- Issued Tickets for Selected Order -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-[#6E635A]">
                                Daftar E-Ticket ({{ $selectedOrder->tickets->count() }})
                            </span>
                            @if($selectedOrder->status === 'paid')
                                <a href="{{ route('orders.ticket', ['order_code' => $selectedOrder->order_code, 'token' => $selectedOrder->access_token]) }}"
                                   target="_blank"
                                   class="link-heritage-tertiary text-xs inline-flex items-center gap-1 group">
                                    <span>Buka Lembar Cetak E-Ticket</span>
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endif
                        </div>

                        @if($selectedOrder->tickets->isEmpty())
                            <p class="text-xs text-[#6E635A] italic">E-Ticket akan dibuat otomatis setelah status pesanan Lunas.</p>
                        @else
                            <div class="divide-y divide-[#E3D9C9] border border-[#E3D9C9]">
                                @foreach($selectedOrder->tickets as $ticket)
                                    <div class="p-2.5 text-xs flex items-center justify-between gap-2">
                                        <div>
                                            <strong class="font-mono text-[#2A211A] block">{{ $ticket->ticket_code }}</strong>
                                            <span class="text-[#6E635A]">{{ $ticket->statusLabel() }}</span>
                                        </div>
                                        @if($ticket->status === 'valid')
                                            <form action="{{ route('admin.tickets.validate') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="code" value="{{ $ticket->ticket_code }}">
                                                <button type="submit" class="px-2.5 py-1 border border-[#6B3F1E] text-[#6B3F1E] font-semibold hover:bg-[#F3EAD8] cursor-pointer">
                                                    Check-In Manual
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

