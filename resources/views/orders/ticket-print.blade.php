@extends('layouts.app')

@section('title', 'E-Ticket Resmi ' . $order->order_code . ' — PT Pabrik Gula Candi Baru')

@section('content')
    <section class="py-10">
        <div class="max-w-[900px] mx-auto px-4 sm:px-6 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E3D9C9] pb-4 no-print">
                <a href="{{ route('orders.show', ['order_code' => $order->order_code, 'token' => $order->access_token]) }}"
                   class="link-heritage-tertiary text-sm">
                    &larr; Kembali ke Rincian Pesanan
                </a>
                <button type="button" onclick="window.print()" class="btn-heritage-primary text-sm">
                    Cetak / Simpan sebagai PDF
                </button>
            </div>

            <!-- Printable Official Heritage E-Ticket Sheet -->
            <div class="border-2 border-[#6B3F1E] bg-[#FFFFFF] p-6 sm:p-8 space-y-6">
                <div class="border-b-2 border-[#E3D9C9] pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                            Dokumen Masuk Resmi &bull; GulaTour Heritage
                        </span>
                        <h1 class="font-serif text-3xl font-bold text-[#6B3F1E]">
                            PT Pabrik Gula Candi Baru
                        </h1>
                        <p class="text-xs text-[#6E635A] mt-0.5">
                            Jl. Raya Candi No. 10, Kec. Candi, Kabupaten Sidoarjo, Jawa Timur 61271
                        </p>
                    </div>
                    <div class="text-left sm:text-right tabular-nums">
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider border border-[#4A6B3A] text-[#4A6B3A] bg-[#4A6B3A]/10 inline-block">
                            LUNAS (PAID)
                        </span>
                        <p class="text-sm font-bold text-[#2A211A] mt-2">Order: {{ $order->order_code }}</p>
                        <p class="text-xs text-[#6E635A]">Diterbitkan: {{ $order->paid_at?->format('d/m/Y H:i') }} WIB</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-[#FAF6EE] border border-[#E3D9C9] p-4 text-sm">
                    <div>
                        <span class="text-xs uppercase text-[#6E635A] block">Event Tour</span>
                        <strong class="text-[#2A211A]">{{ $order->schedule->event->title }}</strong>
                    </div>
                    <div>
                        <span class="text-xs uppercase text-[#6E635A] block">Tanggal & Sesi</span>
                        <strong class="text-[#2A211A] tabular-nums">
                            {{ $order->schedule->tour_date->translatedFormat('d M Y') }} &bull; {{ $order->schedule->session_code }} ({{ $order->schedule->formattedTimeRange() }} WIB)
                        </strong>
                    </div>
                    <div>
                        <span class="text-xs uppercase text-[#6E635A] block">Pemesan Utama</span>
                        <strong class="text-[#2A211A]">{{ $order->buyer_name }} ({{ $order->ticket_qty }} Peserta)</strong>
                    </div>
                </div>

                <div class="divide-y divide-[#E3D9C9]">
                    @foreach($order->tickets as $index => $ticket)
                        <div class="py-5 flex flex-col sm:flex-row items-center justify-between gap-6">
                            <div class="space-y-1.5 flex-1">
                                <span class="text-xs uppercase tracking-wider font-semibold text-[#A67C52]">
                                    Lembar E-Ticket Peserta {{ $index + 1 }} dari {{ $order->ticket_qty }}
                                </span>
                                <h2 class="font-serif text-2xl font-bold text-[#2A211A] tabular-nums">
                                    {{ $ticket->ticket_code }}
                                </h2>
                                <p class="text-xs text-[#6E635A]">
                                    Titik Kumpul: <strong class="text-[#2A211A]">{{ $order->schedule->event->meeting_point }}</strong>
                                </p>
                                <p class="text-xs text-[#4A6B3A] font-semibold">
                                    Status Tiket: {{ $ticket->statusLabel() }}
                                </p>
                                <p class="text-[11px] font-mono text-[#6E635A] pt-1">
                                    Payload: {{ $ticket->qr_payload }}
                                </p>
                            </div>
                            <div class="shrink-0 border border-[#E3D9C9] p-2 bg-[#FFFFFF]">
                                {!! $ticketService->renderSvgQr($ticket->qr_payload, 148) !!}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-[#E3D9C9] pt-4 text-xs text-[#6E635A] flex flex-wrap justify-between gap-2">
                    <span>Harap hadir 15 menit sebelum jadwal sesi dimulai.</span>
                    <span>Verifikasi QR oleh Petugas Admin GulaTour PG Candi Baru.</span>
                </div>
            </div>
        </div>
    </section>

    @if($autoPrint)
        @push('scripts')
            <script>
                window.addEventListener('load', function () {
                    setTimeout(function () { window.print(); }, 350);
                });
            </script>
        @endpush
    @endif
@endsection

