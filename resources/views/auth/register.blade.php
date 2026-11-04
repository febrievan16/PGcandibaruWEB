@extends('layouts.app')

@section('title', 'Daftar Akun Pengunjung — GulaTour PT Pabrik Gula Candi Baru')

@section('content')
    <section class="py-14">
        <div class="max-w-[540px] mx-auto px-4 sm:px-6 space-y-6">
            <div class="border-b border-[#E3D9C9] pb-4 space-y-1">
                <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Modul M5 &bull; Registrasi Opsional (DEC-004)</span>
                <h1 class="font-serif text-3xl font-bold text-[#2A211A]">Daftar Akun Pengunjung</h1>
                <p class="text-sm text-[#6E635A]">
                    Simpan seluruh riwayat pemesanan dan unduh ulang E-Ticket QR Anda kapan saja. Pesanan tamu sebelumnya dengan email yang sama akan otomatis ditautkan.
                </p>
            </div>

            @if($errors->any())
                <div role="alert" class="border border-[#8B2E1F] bg-[#8B2E1F]/10 p-4 text-sm text-[#2A211A]">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST" class="border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-5">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-semibold text-[#2A211A] mb-1.5">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="input-heritage">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-[#2A211A] mb-1.5">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="input-heritage">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-[#2A211A] mb-1.5">Nomor WhatsApp</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="081234567890" class="input-heritage tabular-nums">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#2A211A] mb-1.5">Kata Sandi</label>
                        <input type="password" id="password" name="password" required class="input-heritage">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-[#2A211A] mb-1.5">Konfirmasi Sandi</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required class="input-heritage">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-heritage-primary w-full">
                        Buat Akun & Simpan Riwayat
                    </button>
                </div>

                <div class="text-center text-xs text-[#6E635A]">
                    Sudah memiliki akun? <a href="{{ route('login') }}" class="link-heritage-tertiary">Masuk di sini</a>
                </div>
            </form>
        </div>
    </section>
@endsection

