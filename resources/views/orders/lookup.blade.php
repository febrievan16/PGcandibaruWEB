@extends('layouts.app')

@section('title', 'Cek Status Pesanan & Unduh E-Ticket — GulaTour')

@section('content')
    <section class="py-14">
        <div class="max-w-[640px] mx-auto px-4 sm:px-6 space-y-6">
            <div class="space-y-2 border-b border-[#E3D9C9] pb-5">
                <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Layanan Pengunjung Tamu & Akun</span>
                <h1 class="font-serif text-3xl font-bold text-[#2A211A]">Cek Status Pesanan & E-Ticket</h1>
                <p class="text-sm text-[#6E635A]">
                    Masukkan Kode Order (contoh: <code class="text-[#2A211A]">GT-20261107-DEMO01</code>) beserta alamat email yang digunakan saat pemesanan untuk melihat status pembayaran atau mengunduh ulang E-Ticket.
                </p>
            </div>

            @if($errors->any())
                <div role="alert" class="border border-[#8B2E1F] bg-[#8B2E1F]/10 p-4 text-sm text-[#2A211A]">
                    <strong class="text-xs uppercase tracking-wider text-[#8B2E1F] block mb-1">Pencarian Gagal</strong>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('orders.lookup.submit') }}" method="POST" class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-5">
                @csrf
                <div>
                    <label for="order_code" class="block text-sm font-semibold text-[#2A211A] mb-1.5">
                        Kode Pesanan (Order Code) <span class="text-[#8B2E1F]">*</span>
                    </label>
                    <input type="text"
                           id="order_code"
                           name="order_code"
                           required
                           value="{{ old('order_code') }}"
                           placeholder="GT-YYYYMMDD-XXXXXX"
                           class="input-heritage tabular-nums">
                </div>

                <div>
                    <label for="buyer_email" class="block text-sm font-semibold text-[#2A211A] mb-1.5">
                        Email Pemesan <span class="text-[#8B2E1F]">*</span>
                    </label>
                    <input type="email"
                           id="buyer_email"
                           name="buyer_email"
                           required
                           value="{{ old('buyer_email') }}"
                           placeholder="wisatawan@example.com"
                           class="input-heritage">
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-heritage-primary w-full">
                        Cari Pesanan & Buka E-Ticket
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection

