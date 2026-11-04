@extends('layouts.app')

@section('title', 'Riwayat Pemesanan & E-Ticket Saya — GulaTour')

@section('content')
    <section class="py-12">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 space-y-8">
            <div class="border-b border-[#E3D9C9] pb-5 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Modul M5 &bull; Akun Terdaftar (FR-10)</span>
                    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-[#2A211A] mt-1">Riwayat Pemesanan & E-Ticket</h1>
                    <p class="text-sm text-[#6E635A] mt-1">
                        Masuk sebagai <strong>{{ $user->name }}</strong> ({{ $user->email }})
                    </p>
                </div>
                <a href="{{ route('events.index') }}" class="btn-heritage-primary text-sm">
                    Jelajahi Event Baru
                </a>
            </div>

            @if($orders->isEmpty())
                <!-- Exact Empty State from DESIGN.md §11 -->
                <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-12 text-center space-y-4">
                    <h2 class="font-serif text-2xl text-[#2A211A]">Anda belum memiliki pemesanan.</h2>
                    <p class="text-sm text-[#6E635A]">Pilih jadwal sesi tour aktif untuk mulai memesan tiket kunjungan.</p>
                    <div>
                        <a href="{{ route('events.index') }}" class="btn-heritage-secondary">Jelajahi Event</a>
                    </div>
                </div>
            @else
                <!-- Native Table with Hairline Borders per DESIGN.md §8 -->
                <div class="overflow-x-auto border border-[#E3D9C9] bg-[#FFFFFF]">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-xs uppercase tracking-wider text-[#6E635A]">
                                <th class="py-3.5 px-4 font-semibold">Kode Order</th>
                                <th class="py-3.5 px-4 font-semibold">Event Tour</th>
                                <th class="py-3.5 px-4 font-semibold">Jadwal & Sesi</th>
                                <th class="py-3.5 px-4 font-semibold text-right">Jumlah</th>
                                <th class="py-3.5 px-4 font-semibold text-right">Total Biaya</th>
                                <th class="py-3.5 px-4 font-semibold">Status Pembayaran</th>
                                <th class="py-3.5 px-4 font-semibold text-right">Aksi / E-Ticket</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E3D9C9]">
                            @foreach($orders as $order)
                                <tr class="hover:bg-[#FAF6EE]/60">
                                    <td class="py-4 px-4 font-semibold text-[#2A211A] tabular-nums">
                                        {{ $order->order_code }}
                                    </td>
                                    <td class="py-4 px-4 text-[#2A211A]">
                                        {{ $order->schedule->event->title ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-[#6E635A] tabular-nums">
                                        {{ $order->schedule->tour_date->translatedFormat('d M Y') }}<br>
                                        <span class="text-xs">Sesi {{ $order->schedule->session_code }} ({{ $order->schedule->formattedTimeRange() }})</span>
                                    </td>
                                    <td class="py-4 px-4 text-right tabular-nums font-medium">
                                        {{ $order->ticket_qty }} Tiket
                                    </td>
                                    <td class="py-4 px-4 text-right tabular-nums font-semibold text-[#6B3F1E]">
                                        {{ $order->formattedTotal() }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="px-2.5 py-1 text-xs font-semibold border {{ $order->statusColorClass() }}">
                                            {{ $order->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right space-x-3 whitespace-nowrap">
                                        <a href="{{ route('orders.show', ['order_code' => $order->order_code, 'token' => $order->access_token]) }}"
                                           class="link-heritage-tertiary text-xs">
                                            Detail
                                        </a>
                                        @if($order->status === 'paid')
                                            <a href="{{ route('orders.ticket', ['order_code' => $order->order_code, 'token' => $order->access_token, 'print' => 1]) }}"
                                               target="_blank"
                                               class="btn-heritage-secondary text-xs py-1.5 px-3 min-h-[36px]">
                                                Unduh E-Ticket ({{ $order->tickets_count ?? $order->tickets->count() }})
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection

