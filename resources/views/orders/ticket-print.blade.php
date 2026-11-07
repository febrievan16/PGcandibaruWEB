@extends('layouts.app')

@section('title', 'E-Ticket Resmi ' . $order->order_code . ' — PT Pabrik Gula Candi Baru')

@section('content')
    <style>
        /* Cinema / Thermal Mini Ticket Print Layout */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            html, body {
                background: #FFFFFF !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Hide everything outside the cinema ticket container */
            header, footer, nav, #heritage-scroll-progress, .no-print {
                display: none !important;
            }

            #cinema-ticket-print-wrapper {
                width: 78mm !important;
                max-width: 78mm !important;
                margin: 0 auto !important;
                padding: 4mm 3mm !important;
                box-shadow: none !important;
                border: none !important;
            }

            .cinema-ticket-stub {
                page-break-after: always;
                break-after: page;
                border: 1px dashed #2A211A !important;
                padding: 4mm !important;
                margin-bottom: 0 !important;
                background: #FFFFFF !important;
            }

            .cinema-ticket-stub:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }

        /* Screen Preview Styling */
        .cinema-ticket-stub {
            background: #FFFFFF;
            border: 2px dashed #C8A24A;
            position: relative;
        }

        .cinema-ticket-stub::before,
        .cinema-ticket-stub::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #FAF6EE;
            top: 50%;
            transform: translateY(-50%);
            border: 1px dashed #C8A24A;
            z-index: 10;
        }

        .cinema-ticket-stub::before {
            left: -9px;
        }

        .cinema-ticket-stub::after {
            right: -9px;
        }

        @media print {
            .cinema-ticket-stub::before,
            .cinema-ticket-stub::after {
                display: none !important;
            }
        }
    </style>

    <section class="py-10">
        <div class="max-w-[720px] mx-auto px-4 sm:px-6 space-y-6">
            <!-- Screen Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E3D9C9] pb-4 no-print">
                <a href="{{ route('orders.show', ['order_code' => $order->order_code, 'token' => $order->access_token]) }}"
                   class="link-heritage-tertiary text-sm">
                    &larr; Kembali ke Rincian Pesanan
                </a>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#6E635A] hidden sm:inline">Format Cetak: Tiket Saku Bioskop / Thermal (80mm)</span>
                    <button type="button" onclick="window.print()" class="btn-heritage-primary text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Tiket Bioskop (Print)</span>
                    </button>
                </div>
            </div>

            <!-- Cinema Mini Ticket Container -->
            <div id="cinema-ticket-print-wrapper" class="space-y-6">
                @foreach($order->tickets as $index => $ticket)
                    <div class="cinema-ticket-stub max-w-[340px] sm:max-w-[360px] mx-auto p-5 sm:p-6 shadow-md print:shadow-none space-y-4">
                        <!-- Header Bioskop Heritage -->
                        <div class="text-center border-b-2 border-dashed border-[#2A211A] pb-3 space-y-1">
                            <span class="text-[10px] uppercase tracking-[0.14em] font-bold text-[#6B3F1E] block">
                                E-TICKET RESMI &bull; ANNO 1911
                            </span>
                            <h1 class="font-serif text-lg font-bold text-[#2A211A] leading-tight">
                                PT Pabrik Gula Candi Baru
                            </h1>
                            <p class="text-[10px] text-[#6E635A]">
                                Wisata Edukasi Heritage &bull; Candi, Sidoarjo
                            </p>
                        </div>

                        <!-- Info Acara & Jadwal -->
                        <div class="text-center space-y-1 pt-1">
                            <span class="text-[10px] uppercase tracking-wider text-[#A67C52] font-semibold block">
                                Program Kunjungan:
                            </span>
                            <h2 class="font-serif text-base font-bold text-[#2A211A] leading-snug">
                                {{ $order->schedule->event->title }}
                            </h2>
                            <div class="inline-block bg-[#F3EAD8] print:bg-transparent border border-[#6B3F1E] px-2.5 py-0.5 text-xs font-bold text-[#6B3F1E] tabular-nums mt-1">
                                {{ $order->schedule->tour_date->translatedFormat('d M Y') }} &bull; Sesi {{ $order->schedule->session_code }}
                            </div>
                            <p class="text-[11px] font-semibold text-[#2A211A] tabular-nums">
                                {{ $order->schedule->formattedTimeRange() }} WIB
                            </p>
                        </div>

                        <!-- QR Code Area (Cinema Style Scanner Box) -->
                        <div class="text-center space-y-2 py-2 border-y border-dashed border-[#E3D9C9] print:border-[#2A211A]">
                            <div class="flex justify-center">
                                <div class="p-2 bg-[#FFFFFF] border border-[#2A211A] inline-block">
                                    {!! $ticketService->renderSvgQr($ticket->qr_payload, 150) !!}
                                </div>
                            </div>
                            <div>
                                <span class="font-mono text-xs font-bold text-[#2A211A] tracking-wider block tabular-nums">
                                    {{ $ticket->ticket_code }}
                                </span>
                                <span class="text-[10px] text-[#4A6B3A] font-semibold uppercase tracking-wider block">
                                    Status: {{ $ticket->statusLabel() }} (Lunas)
                                </span>
                            </div>
                        </div>

                        <!-- Detail Seat / Pass Holder -->
                        <div class="grid grid-cols-2 gap-2 text-xs tabular-nums border-b-2 border-dashed border-[#2A211A] pb-3">
                            <div>
                                <span class="text-[10px] uppercase text-[#6E635A] block">Pemesan:</span>
                                <strong class="text-[#2A211A] block truncate">{{ $order->buyer_name }}</strong>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase text-[#6E635A] block">Tiket Ke:</span>
                                <strong class="text-[#6B3F1E] block font-bold text-sm">{{ $index + 1 }} / {{ $order->ticket_qty }}</strong>
                            </div>
                            <div class="col-span-2 pt-1">
                                <span class="text-[10px] uppercase text-[#6E635A] block">Titik Kumpul:</span>
                                <span class="text-[#2A211A] font-medium text-[11px] block leading-tight">{{ $order->schedule->event->meeting_point }}</span>
                            </div>
                        </div>

                        <!-- Footer Struk Bioskop -->
                        <div class="text-center space-y-1 text-[9px] text-[#6E635A] leading-tight">
                            <p class="font-mono text-[10px] text-[#2A211A]">Order ID: {{ $order->order_code }}</p>
                            <p>Tunjukkan QR ini kepada petugas pintu masuk.</p>
                            <p>Harap hadir 15 menit sebelum sesi dimulai.</p>
                            <p class="pt-1 font-semibold text-[#2A211A]">&bull; Selamat Menikmati Tour Heritage &bull;</p>
                        </div>
                    </div>
                @endforeach
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

