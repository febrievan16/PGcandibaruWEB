@extends('layouts.admin')

@section('title', 'Validasi QR E-Ticket (Check-In) — Admin GulaTour')
@section('page_title', 'Pemindaian & Validasi QR E-Ticket Pengunjung')
@section('page_subtitle', 'FR-14 & TASK-13: Pindai payload QR atau masukkan Kode Tiket untuk mencatat kehadiran (status Valid -> Used).')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left 6/12: Scanner Form & Result -->
        <div class="lg:col-span-6 space-y-6">
            @if(session('scan_result'))
                @php $res = session('scan_result'); @endphp
                <div role="alert" class="border-2 p-5 {{ $res['valid'] ? 'border-[#4A6B3A] bg-[#4A6B3A]/10' : 'border-[#8B2E1F] bg-[#8B2E1F]/10' }}">
                    <span class="text-xs font-bold uppercase tracking-wider {{ $res['valid'] ? 'text-[#4A6B3A]' : 'text-[#8B2E1F]' }} block">
                        {{ $res['valid'] ? 'VALIDASI BERHASIL (CHECK-IN TERCATAT)' : 'VALIDASI DITOLAK: ' . $res['code'] }}
                    </span>
                    <p class="font-serif text-xl font-bold text-[#2A211A] mt-1">
                        {{ $res['message'] }}
                    </p>
                    @if(!empty($res['ticket']))
                        <dl class="mt-3 pt-3 border-t border-[#E3D9C9] text-xs space-y-1 tabular-nums">
                            <div>Kode Tiket: <strong>{{ data_get($res, 'ticket.ticket_code', '-') }}</strong></div>
                            <div>Pemesan: <strong>{{ data_get($res, 'ticket.buyer_name', data_get($res, 'ticket.order.buyer_name', '-')) }}</strong> ({{ data_get($res, 'ticket.order_code', data_get($res, 'ticket.order.order_code', '-')) }})</div>
                            <div>Event: <strong>{{ data_get($res, 'ticket.event_title', data_get($res, 'ticket.order.schedule.event.title', '-')) }}</strong></div>
                        </dl>
                    @endif
                </div>
            @endif

            <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-5">
                <h2 class="font-serif text-xl font-bold text-[#2A211A]">Pindai / Masukkan Payload QR atau Kode Tiket</h2>
                <p class="text-xs text-[#6E635A]">
                    Arahkan pemindai barcode/QR ke kolom di bawah ini atau tempelkan kode tiket (contoh: <code>TKT-...</code> atau <code>GULATOUR|...</code>).
                </p>

                <form action="{{ route('admin.tickets.validate') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="code" class="block text-sm font-semibold text-[#2A211A] mb-1.5">
                            Payload QR / Kode Tiket <span class="text-[#8B2E1F]">*</span>
                        </label>
                        <input type="text"
                               id="code"
                               name="code"
                               required
                               autofocus
                               value="{{ old('code') }}"
                               placeholder="GULATOUR|GT-...|TKT-...|... atau TKT-..."
                               class="input-heritage font-mono text-sm">
                    </div>

                    <button type="submit" class="btn-heritage-primary w-full">
                        Validasi & Check-In Sekarang
                    </button>
                </form>

                <!-- Quick Test Helper for Valid Tickets -->
                @php
                    $sampleValidTickets = \App\Models\Ticket::with('order')->where('status', 'valid')->limit(4)->get();
                @endphp
                @if($sampleValidTickets->isNotEmpty())
                    <div class="pt-4 border-t border-[#E3D9C9] space-y-2">
                        <span class="text-xs uppercase tracking-wider text-[#6B3F1E] font-semibold block">
                            Tiket Aktif Siap Uji Scan (Klik untuk Mengisi):
                        </span>
                        <div class="space-y-1.5">
                            @foreach($sampleValidTickets as $sample)
                                <button type="button"
                                        onclick="document.getElementById('code').value='{{ $sample->qr_payload }}';"
                                        class="w-full text-left p-2.5 text-xs border border-[#E3D9C9] bg-[#FAF6EE] hover:border-[#6B3F1E] cursor-pointer flex items-center justify-between">
                                    <span class="font-mono font-semibold text-[#2A211A]">{{ $sample->ticket_code }}</span>
                                    <span class="text-[#6E635A]">{{ $sample->order->buyer_name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right 6/12: Recent Check-Ins Log -->
        <div class="lg:col-span-6 space-y-3">
            <h2 class="font-serif text-xl font-bold text-[#2A211A]">Riwayat Check-In Terbaru</h2>
            <div class="border border-[#E3D9C9] bg-[#FFFFFF] overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E3D9C9] bg-[#FAF6EE] uppercase tracking-wider text-[#6E635A]">
                            <th class="py-3 px-3.5 font-semibold">Waktu Check-In</th>
                            <th class="py-3 px-3.5 font-semibold">Kode Tiket</th>
                            <th class="py-3 px-3.5 font-semibold">Peserta / Order</th>
                            <th class="py-3 px-3.5 font-semibold">Sesi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3D9C9]">
                        @forelse($recentCheckIns as $checked)
                            <tr>
                                <td class="py-3 px-3.5 tabular-nums text-[#4A6B3A] font-semibold">
                                    {{ $checked->checked_in_at?->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="py-3 px-3.5 font-mono text-[#2A211A]">{{ $checked->ticket_code }}</td>
                                <td class="py-3 px-3.5">
                                    <strong class="text-[#2A211A] block">{{ $checked->order->buyer_name }}</strong>
                                    <span class="text-[#6E635A]">{{ $checked->order->order_code }}</span>
                                </td>
                                <td class="py-3 px-3.5 tabular-nums text-[#6E635A]">
                                    {{ $checked->order->schedule->tour_date->format('Y-m-d') }} ({{ $checked->order->schedule->session_code }})
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-[#6E635A]">Belum ada tiket yang di-scan hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

