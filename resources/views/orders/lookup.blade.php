@extends('layouts.app')

@section('title', 'Cek Status Pesanan & Unduh E-Ticket — GulaTour')

@section('content')
    <section class="relative overflow-hidden py-14 lg:py-20">
        <div class="relative z-10 max-w-[640px] mx-auto px-4 sm:px-6 space-y-6">
            <div data-reveal="up" class="space-y-2 border-b border-[#E3D9C9] pb-5">
                <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Layanan Pengunjung &amp; E-Ticket Resmi</span>
                <h1 class="font-serif text-3xl font-bold text-[#2A211A]">Cek Status Pesanan &amp; E-Ticket</h1>
                <p class="text-sm text-[#6E635A] leading-relaxed">
                    Masukkan Kode Order beserta alamat email yang Anda daftarkan saat reservasi untuk melihat rincian pembayaran atau mengunduh ulang tiket elektronik ber-QR Code.
                </p>
            </div>

            @if($errors->any())
                <div role="alert" data-reveal="scale" class="border border-[#8B2E1F] bg-[#8B2E1F]/10 p-4 text-sm text-[#2A211A]">
                    <strong class="text-xs uppercase tracking-wider text-[#8B2E1F] block mb-1">Pencarian Tidak Ditemukan</strong>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('orders.lookup.submit') }}" method="POST" class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 sm:p-8 space-y-5 shadow-xs">
                @csrf
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="order_code" class="block text-sm font-semibold text-[#2A211A]">
                            Kode Pesanan (Order Code) <span class="text-[#8B2E1F]">*</span>
                        </label>
                        <span class="text-[11px] text-[#6E635A] tabular-nums">Contoh: GT-YYYYMMDD-XXXXXX</span>
                    </div>
                    <input type="text"
                           id="order_code"
                           name="order_code"
                           required
                           value="{{ old('order_code') }}"
                           placeholder="GT-20261107-DEMO01"
                           class="input-heritage tabular-nums uppercase">
                </div>

                <div>
                    <label for="buyer_email" class="block text-sm font-semibold text-[#2A211A] mb-1.5">
                        Alamat Email Pemesan <span class="text-[#8B2E1F]">*</span>
                    </label>
                    <input type="email"
                           id="buyer_email"
                           name="buyer_email"
                           required
                           value="{{ old('buyer_email') }}"
                           placeholder="nama@domain.com"
                           class="input-heritage">
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-heritage-primary w-full inline-flex items-center justify-center gap-2 group min-h-[46px]">
                        <span>Cari Pesanan &amp; Buka E-Ticket</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Reassurance & Support Box -->
            <div class="border border-[#E3D9C9] bg-[#FAF6EE] p-4 text-xs text-[#6E635A] space-y-2">
                <div class="flex items-center gap-2 font-semibold text-[#6B3F1E]">
                    <span class="w-1.5 h-1.5 bg-[#C8A24A]"></span>
                    <span>Butuh Bantuan Reservasi Wisata?</span>
                </div>
                <p class="leading-relaxed">
                    Jika Anda tidak menemukan kode order atau email konfirmasi masuk ke kotak spam, silakan hubungi loket tiket resmi PT Pabrik Gula Candi Baru melalui layanan operasional di Kecamatan Candi, Sidoarjo.
                </p>
            </div>
        </div>
    </section>
@endsection

