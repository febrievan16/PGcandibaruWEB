@extends('layouts.app')

@section('title', 'Masuk Akun — GulaTour PT Pabrik Gula Candi Baru')

@section('content')
    <section class="relative overflow-hidden py-14">
        <div class="relative z-10 max-w-[540px] mx-auto px-4 sm:px-6 space-y-6">
            <div data-reveal="up" class="border-b border-[#E3D9C9] pb-4 space-y-1">
                <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Portal Layanan &bull; Akses Akun</span>
                <h1 class="font-serif text-3xl font-bold text-[#2A211A]">Masuk ke GulaTour</h1>
                <p class="text-sm text-[#6E635A]">
                    Masuk untuk melihat riwayat pemesanan E-Ticket atau mengelola operasional sebagai Administrator. (Pengunjung tamu tetap dapat memesan tiket tanpa akun).
                </p>
            </div>

            @if($errors->any())
                <div role="alert" data-reveal="scale" class="border border-[#8B2E1F] bg-[#8B2E1F]/10 p-4 text-sm text-[#2A211A]">
                    {{ $errors->first() }}
                </div>
            @endif

            <div data-reveal="up" data-reveal-delay="110" class="heritage-card-interactive border border-[#E3D9C9] bg-[#FFFFFF] p-6 space-y-5">
                <!-- Single Sign-On (Google via Firebase) -->
                @include('auth.partials.firebase-google-auth', ['buttonText' => 'Masuk Cepat dengan Akun Google', 'idPrefix' => 'login'])

                <div class="relative flex items-center justify-center">
                    <div class="border-t border-[#E3D9C9] w-full"></div>
                    <span class="bg-[#FFFFFF] px-3 text-xs text-[#6E635A] uppercase tracking-wider whitespace-nowrap">atau masuk dengan email</span>
                    <div class="border-t border-[#E3D9C9] w-full"></div>
                </div>

                <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#2A211A] mb-1.5">Alamat Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="input-heritage">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#2A211A] mb-1.5">Kata Sandi</label>
                        <input type="password" id="password" name="password" required class="input-heritage">
                    </div>

                    <div class="flex items-center justify-between text-xs text-[#6E635A]">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="accent-[#6B3F1E]">
                            <span>Ingat sesi saya</span>
                        </label>
                        <a href="{{ route('register') }}" class="link-heritage-tertiary">Belum punya akun? Daftar</a>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="btn-heritage-primary w-full">
                            Masuk Sekarang
                        </button>
                    </div>
                </form>
            </div>

            <!-- Demo Credentials Helper -->
            <div class="border border-[#E3D9C9] bg-[#F3EAD8] p-4 text-xs space-y-2">
                <strong class="uppercase tracking-wider text-[#6B3F1E] block">Akun Demo Tersedia (Klik untuk Mengisi Otomatis):</strong>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                    <button type="button"
                            onclick="document.getElementById('email').value='admin@pgcandibaru.co.id';document.getElementById('password').value='password';"
                            class="text-left p-2.5 border border-[#E3D9C9] bg-[#FFFFFF] hover:border-[#6B3F1E] cursor-pointer">
                        <strong class="text-[#2A211A] block">Admin PG Candi Baru</strong>
                        <span class="text-[#6E635A]">admin@pgcandibaru.co.id / password</span>
                    </button>
                    <button type="button"
                            onclick="document.getElementById('email').value='wisatawan@example.com';document.getElementById('password').value='password';"
                            class="text-left p-2.5 border border-[#E3D9C9] bg-[#FFFFFF] hover:border-[#6B3F1E] cursor-pointer">
                        <strong class="text-[#2A211A] block">Pengunjung Terdaftar</strong>
                        <span class="text-[#6E635A]">wisatawan@example.com / password</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
@endsection

