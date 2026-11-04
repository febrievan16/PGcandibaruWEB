@extends('layouts.app')

@section('title', 'Pesanan ' . $order->order_code . ' — Checkout & E-Ticket | GulaTour')

@section('content')
    <section class="py-12">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 space-y-8">
            <!-- Header & Explicit Status Label + Color (DESIGN.md §4 & §12) -->
            <div class="border-b border-[#E3D9C9] pb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">
                        Modul M4 &bull; Rincian Pemesanan & Pembayaran
                    </span>
                    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-[#2A211A] mt-1 tabular-nums">
                        Order #{{ $order->order_code }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <span class="px-4 py-2 text-sm font-semibold border {{ $order->statusColorClass() }}">
                        Status: {{ $order->statusLabel() }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left 7/12: Payment Action (if pending) or Issued QR E-Tickets (if paid) -->
                <div class="lg:col-span-7 space-y-6">
                    @if($order->status === 'pending')
                        <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-6">
                            <div class="border-b border-[#E3D9C9] pb-4 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span class="text-xs uppercase tracking-wider text-[#B8860B] font-semibold block">
                                        Menunggu Pembayaran Online (DEC-002)
                                    </span>
                                    <h2 class="font-serif text-2xl font-bold text-[#2A211A]">
                                        Selesaikan Pembayaran via Midtrans
                                    </h2>
                                </div>
                                @if($order->expires_at)
                                    <div class="text-right text-xs text-[#6E635A] tabular-nums">
                                        Batas Waktu Pembayaran:<br>
                                        <strong class="text-[#2A211A]">{{ $order->expires_at->translatedFormat('d M Y, H:i') }} WIB</strong>
                                    </div>
                                @endif
                            </div>

                            <p class="text-sm text-[#6E635A] leading-relaxed">
                                Kuota sebanyak <strong>{{ $order->ticket_qty }} kursi</strong> telah dikunci untuk pesanan Anda. Pilih kanal pembayaran di bawah ini (Virtual Account Bank, QRIS / E-Wallet, atau Kartu Kredit) untuk menerbitkan E-Ticket ber-QR Code secara otomatis.
                            </p>

                            <!-- Midtrans Snap / Built-In Sandbox Payment Simulator -->
                            <form action="{{ route('orders.simulate-payment', ['order_code' => $order->order_code]) }}" method="POST" class="space-y-5">
                                @csrf
                                <input type="hidden" name="token" value="{{ $order->access_token }}">
                                <input type="hidden" name="transaction_status" value="settlement">

                                <fieldset class="space-y-3">
                                    <legend class="text-xs uppercase tracking-wider font-semibold text-[#2A211A] mb-2">
                                        Pilih Metode Pembayaran (Midtrans Payment Channels):
                                    </legend>

                                    <label class="flex items-center justify-between p-3.5 border border-[#E3D9C9] bg-[#FAF6EE] hover:border-[#6B3F1E] cursor-pointer">
                                        <span class="flex items-center gap-3">
                                            <input type="radio" name="payment_type" value="bank_transfer_bca" checked class="accent-[#6B3F1E]">
                                            <span>
                                                <strong class="text-sm text-[#2A211A] block">Virtual Account Bank (BCA / Mandiri / BNI / BRI)</strong>
                                                <span class="text-xs text-[#6E635A]">Verifikasi otomatis 24 jam via Midtrans</span>
                                            </span>
                                        </span>
                                        <span class="text-xs font-semibold text-[#6B3F1E]">VA</span>
                                    </label>

                                    <label class="flex items-center justify-between p-3.5 border border-[#E3D9C9] bg-[#FAF6EE] hover:border-[#6B3F1E] cursor-pointer">
                                        <span class="flex items-center gap-3">
                                            <input type="radio" name="payment_type" value="ewallet_qris" class="accent-[#6B3F1E]">
                                            <span>
                                                <strong class="text-sm text-[#2A211A] block">E-Wallet & QRIS (GoPay / ShopeePay / QRIS)</strong>
                                                <span class="text-xs text-[#6E635A]">Pindai kode QRIS instan</span>
                                            </span>
                                        </span>
                                        <span class="text-xs font-semibold text-[#6B3F1E]">E-Wallet</span>
                                    </label>

                                    <label class="flex items-center justify-between p-3.5 border border-[#E3D9C9] bg-[#FAF6EE] hover:border-[#6B3F1E] cursor-pointer">
                                        <span class="flex items-center gap-3">
                                            <input type="radio" name="payment_type" value="credit_card" class="accent-[#6B3F1E]">
                                            <span>
                                                <strong class="text-sm text-[#2A211A] block">Kartu Kredit / Debit Online (Visa / Mastercard / JCB)</strong>
                                                <span class="text-xs text-[#6E635A]">3D Secure PCI-DSS oleh Midtrans</span>
                                            </span>
                                        </span>
                                        <span class="text-xs font-semibold text-[#6B3F1E]">Card</span>
                                    </label>
                                </fieldset>

                                <div class="pt-2 flex flex-wrap items-center justify-between gap-4">
                                    <div class="text-xs text-[#6E635A] tabular-nums">
                                        Snap Token: <code class="text-[#2A211A]">{{ $order->snap_token }}</code>
                                    </div>
                                    <!-- Single Primary CTA in Viewport per DESIGN.md §9 -->
                                    <button type="submit" class="btn-heritage-primary text-base px-8">
                                        Bayar Sekarang ({{ $order->formattedTotal() }})
                                    </button>
                                </div>
                            </form>

                            <!-- Secondary Sandbox Test Actions (Simulate Expire / Cancel) -->
                            <div class="pt-4 border-t border-[#E3D9C9] flex flex-wrap items-center justify-between gap-3 text-xs text-[#6E635A]">
                                <span>Simulasi Status Lain (Pengujian Sandbox):</span>
                                <div class="flex items-center gap-3">
                                    <form action="{{ route('orders.simulate-payment', ['order_code' => $order->order_code]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $order->access_token }}">
                                        <input type="hidden" name="transaction_status" value="expire">
                                        <button type="submit" class="underline hover:text-[#8B2E1F] cursor-pointer">
                                            Simulasikan Kedaluwarsa
                                        </button>
                                    </form>
                                    <span>&bull;</span>
                                    <form action="{{ route('orders.simulate-payment', ['order_code' => $order->order_code]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $order->access_token }}">
                                        <input type="hidden" name="transaction_status" value="cancel">
                                        <button type="submit" class="underline hover:text-[#8B2E1F] cursor-pointer">
                                            Batalkan Pesanan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @elseif($order->status === 'paid')
                        <!-- Issued QR E-Tickets (FR-07) -->
                        <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-6">
                            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E3D9C9] pb-4">
                                <div>
                                    <span class="text-xs uppercase tracking-wider text-[#4A6B3A] font-semibold block">
                                        Pembayaran Terverifikasi Lunas &bull; E-Ticket Resmi
                                    </span>
                                    <h2 class="font-serif text-2xl font-bold text-[#2A211A]">
                                        Daftar E-Ticket & Kode QR Peserta ({{ $order->tickets->count() }} Tiket)
                                    </h2>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('orders.ticket', ['order_code' => $order->order_code, 'token' => $order->access_token, 'print' => 1]) }}"
                                       target="_blank"
                                       class="btn-heritage-primary text-sm">
                                        Cetak / Unduh PDF E-Ticket
                                    </a>
                                </div>
                            </div>

                            <p class="text-sm text-[#6E635A]">
                                Tunjukkan kode QR di bawah ini kepada petugas di <strong>{{ $order->schedule->event->meeting_point ?? 'Gerbang Utama PG Candi Baru' }}</strong> untuk pemindaian check-in. Tautan E-Ticket juga telah dikirimkan ke <strong>{{ $order->buyer_email }}</strong> dan WhatsApp <strong>{{ $order->buyer_phone }}</strong>.
                            </p>

                            <div class="space-y-4">
                                @foreach($order->tickets as $idx => $ticket)
                                    <div class="border border-[#E3D9C9] bg-[#FAF6EE] p-4 flex flex-col sm:flex-row items-center justify-between gap-6">
                                        <div class="space-y-2 flex-1">
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="font-semibold uppercase tracking-wider text-[#6B3F1E]">
                                                    Tiket Peserta #{{ $idx + 1 }}
                                                </span>
                                                <span>&bull;</span>
                                                <span class="font-semibold {{ $ticket->status === 'valid' ? 'text-[#4A6B3A]' : 'text-[#6E635A]' }}">
                                                    {{ $ticket->statusLabel() }}
                                                </span>
                                            </div>
                                            <p class="font-serif text-xl font-bold text-[#2A211A] tabular-nums">
                                                {{ $ticket->ticket_code }}
                                            </p>
                                            <p class="text-xs text-[#6E635A] tabular-nums">
                                                Pemesan: <strong class="text-[#2A211A]">{{ $order->buyer_name }}</strong><br>
                                                Jadwal: {{ $order->schedule->tour_date->translatedFormat('d M Y') }} &bull; Sesi {{ $order->schedule->session_code }} ({{ $order->schedule->formattedTimeRange() }} WIB)
                                            </p>
                                            <div class="pt-1">
                                                <code class="text-[11px] bg-[#FFFFFF] border border-[#E3D9C9] px-2 py-0.5 text-[#6E635A] select-all block sm:inline-block">
                                                    {{ $ticket->qr_payload }}
                                                </code>
                                            </div>
                                        </div>

                                        <div class="shrink-0 bg-[#FFFFFF] p-2 border border-[#E3D9C9]">
                                            {!! $ticketService->renderSvgQr($ticket->qr_payload, 136) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    @else
                        <!-- Expired or Cancelled Error/Status Box (DESIGN.md §11) -->
                        <div class="border border-[#8B2E1F] bg-[#FFFFFF] p-6 space-y-4">
                            <span class="text-xs uppercase tracking-wider text-[#8B2E1F] font-semibold block">
                                Status Pesanan: {{ $order->statusLabel() }}
                            </span>
                            <h2 class="font-serif text-2xl font-bold text-[#2A211A]">
                                Pesanan #{{ $order->order_code }} Tidak Aktif
                            </h2>
                            <p class="text-sm text-[#6E635A] leading-relaxed">
                                Pesanan ini telah berstatus <strong>{{ $order->statusLabel() }}</strong> dan kuota kursi yang sebelumnya dikunci telah dikembalikan ke sistem. Silakan buat pesanan baru jika Anda masih ingin mengikuti sesi tur.
                            </p>
                            <div class="pt-2">
                                <a href="{{ route('events.show', $order->schedule->event->slug) }}" class="btn-heritage-primary">
                                    Coba Lagi / Pilih Sesi Baru
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right 5/12: Order Metadata Summary -->
                <div class="lg:col-span-5">
                    <div class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-4">
                        <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold block">
                            Rincian Pesanan Resmi
                        </span>
                        <h3 class="font-serif text-xl font-bold text-[#2A211A]">
                            {{ $order->schedule->event->title }}
                        </h3>

                        <dl class="divide-y divide-[#E3D9C9] border-t border-b border-[#E3D9C9] text-sm">
                            <div class="py-3 flex justify-between gap-4">
                                <dt class="text-[#6E635A]">Kode Order</dt>
                                <dd class="font-semibold text-[#2A211A] tabular-nums">{{ $order->order_code }}</dd>
                            </div>
                            <div class="py-3 flex justify-between gap-4">
                                <dt class="text-[#6E635A]">Nama Pemesan</dt>
                                <dd class="font-semibold text-[#2A211A]">{{ $order->buyer_name }}</dd>
                            </div>
                            <div class="py-3 flex justify-between gap-4">
                                <dt class="text-[#6E635A]">Email Pengiriman</dt>
                                <dd class="text-[#2A211A]">{{ $order->buyer_email }}</dd>
                            </div>
                            <div class="py-3 flex justify-between gap-4">
                                <dt class="text-[#6E635A]">Nomor WhatsApp</dt>
                                <dd class="text-[#2A211A] tabular-nums">{{ $order->buyer_phone }}</dd>
                            </div>
                            <div class="py-3 flex justify-between gap-4">
                                <dt class="text-[#6E635A]">Tanggal Tour</dt>
                                <dd class="font-semibold text-[#2A211A] tabular-nums">{{ $order->schedule->tour_date->translatedFormat('d F Y') }}</dd>
                            </div>
                            <div class="py-3 flex justify-between gap-4">
                                <dt class="text-[#6E635A]">Sesi</dt>
                                <dd class="font-semibold text-[#2A211A] tabular-nums">{{ $order->schedule->session_code }} ({{ $order->schedule->formattedTimeRange() }} WIB)</dd>
                            </div>
                            <div class="py-3 flex justify-between gap-4">
                                <dt class="text-[#6E635A]">Jumlah Tiket</dt>
                                <dd class="font-semibold text-[#2A211A] tabular-nums">{{ $order->ticket_qty }} × Rp {{ number_format($order->unit_price, 0, ',', '.') }}</dd>
                            </div>
                            @if($order->paid_at)
                                <div class="py-3 flex justify-between gap-4">
                                    <dt class="text-[#6E635A]">Waktu Pelunasan</dt>
                                    <dd class="text-[#4A6B3A] font-semibold tabular-nums">{{ $order->paid_at->translatedFormat('d M Y, H:i') }} WIB</dd>
                                </div>
                            @endif
                        </dl>

                        <div class="flex items-baseline justify-between pt-1">
                            <span class="text-sm font-semibold text-[#2A211A]">Total Nominal</span>
                            <span class="font-serif text-2xl font-bold text-[#6B3F1E] tabular-nums">
                                {{ $order->formattedTotal() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

