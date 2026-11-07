<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GulaTour — Wisata Heritage PT Pabrik Gula Candi Baru')</title>
    <meta name="description" content="Portal resmi kunjungan edukasi dan wisata sejarah PT Pabrik Gula Candi Baru, Sidoarjo (Berdiri 1911). Pilih jadwal sesi tur dan pesan e-ticket resmi secara daring.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-[#FAF6EE] text-[#2A211A] antialiased selection:bg-[#C8A24A]/30 overflow-x-hidden">
    <!-- Top Scroll-Driven Progress Indicator -->
    <div id="heritage-scroll-progress" aria-hidden="true"></div>

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:bg-[#C8A24A] focus:text-[#2A211A] focus:px-4 focus:py-2 focus:font-semibold">
        Lewati ke konten utama
    </a>

    <!-- Top Heritage Editorial Utility Bar -->
    <div class="border-b border-[#E3D9C9] bg-[#FAF6EE] text-[13px] text-[#6E635A] no-print">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-2 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-4">
                <span class="uppercase tracking-[0.06em] font-medium text-[#6B3F1E]">Warisan Industri Gula Sejak 1911</span>
                <span class="hidden sm:inline text-[#E3D9C9]">|</span>
                <span class="hidden sm:inline">Kecamatan Candi, Sidoarjo — Jawa Timur</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('orders.lookup') }}" class="hover:text-[#6B3F1E] underline underline-offset-4">Cek Status Pesanan / E-Ticket</a>
                @auth
                    <span class="text-[#E3D9C9]">|</span>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="font-semibold text-[#6B3F1E] hover:underline">Dashboard Admin</a>
                    @else
                        <a href="{{ route('account.orders') }}" class="font-semibold text-[#6B3F1E] hover:underline">Riwayat Saya ({{ auth()->user()->name }})</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Editorial Header -->
    <header data-sticky-header class="border-b border-[#E3D9C9] bg-[#FAF6EE]/95 sticky top-0 z-40 backdrop-blur-xs transition-all duration-300 no-print">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-6">
            <a href="{{ route('home') }}" class="group flex flex-col">
                <span class="font-serif text-2xl sm:text-[28px] font-bold tracking-tight text-[#6B3F1E] leading-none">
                    GulaTour
                </span>
                <span class="text-[12px] uppercase tracking-[0.08em] text-[#A67C52] font-semibold mt-1">
                    PT Pabrik Gula Candi Baru
                </span>
            </a>

            <!-- Desktop Navigation -->
            <nav aria-label="Navigasi Utama" class="hidden md:flex items-center gap-8 text-[15px]">
                <a href="{{ route('home') }}"
                   class="py-2 font-medium transition-colors {{ request()->routeIs('home') ? 'text-[#6B3F1E] font-semibold border-b-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:text-[#6B3F1E]' }}">
                    Beranda
                </a>
                <a href="{{ route('profile') }}"
                   class="py-2 font-medium transition-colors {{ request()->routeIs('profile') ? 'text-[#6B3F1E] font-semibold border-b-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:text-[#6B3F1E]' }}">
                    Profil & Sejarah Pabrik
                </a>
                <a href="{{ route('events.index') }}"
                   class="py-2 font-medium transition-colors {{ request()->routeIs('events.*') ? 'text-[#6B3F1E] font-semibold border-b-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:text-[#6B3F1E]' }}">
                    Jadwal & Katalog Tour
                </a>
                <a href="{{ route('orders.lookup') }}"
                   class="py-2 font-medium transition-colors {{ request()->routeIs('orders.lookup') ? 'text-[#6B3F1E] font-semibold border-b-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:text-[#6B3F1E]' }}">
                    Cek E-Ticket
                </a>
            </nav>

            <!-- Right Auth / Action -->
            <div class="hidden md:flex items-center gap-3">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-heritage-secondary text-sm">
                            Panel Admin
                        </a>
                    @else
                        <a href="{{ route('account.orders') }}" class="btn-heritage-secondary text-sm">
                            Riwayat Tiket
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm text-[#6E635A] hover:text-[#8B2E1F] cursor-pointer min-h-[44px]">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-medium text-[#6B3F1E] hover:underline underline-offset-4 min-h-[44px] inline-flex items-center">
                        Masuk Akun
                    </a>
                    @if(!request()->routeIs('events.show') && !request()->routeIs('orders.show'))
                        <a href="{{ route('events.index') }}" class="btn-heritage-primary text-sm">
                            Pesan Tiket Tour
                        </a>
                    @endif
                @endauth
            </div>

            <!-- Mobile Hamburger Button (< 640px / md) -->
            <button type="button"
                    id="mobile-menu-toggle"
                    aria-expanded="false"
                    aria-controls="mobile-navigation-drawer"
                    class="md:hidden inline-flex items-center justify-center w-11 h-11 border border-[#E3D9C9] text-[#6B3F1E] rounded-[2px]">
                <span class="sr-only">Buka menu navigasi</span>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-navigation-drawer" class="hidden md:hidden border-t border-[#E3D9C9] bg-[#FAF6EE] px-4 pt-3 pb-6 space-y-3">
            <a href="{{ route('home') }}" class="block py-2.5 border-b border-[#E3D9C9] font-medium text-[#2A211A]">Beranda</a>
            <a href="{{ route('profile') }}" class="block py-2.5 border-b border-[#E3D9C9] font-medium text-[#2A211A]">Profil & Sejarah Pabrik</a>
            <a href="{{ route('events.index') }}" class="block py-2.5 border-b border-[#E3D9C9] font-medium text-[#2A211A]">Jadwal & Katalog Tour</a>
            <a href="{{ route('orders.lookup') }}" class="block py-2.5 border-b border-[#E3D9C9] font-medium text-[#2A211A]">Cek Pesanan / E-Ticket</a>
            <div class="pt-2 flex flex-col gap-2">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-heritage-secondary w-full">Dashboard Admin</a>
                    @else
                        <a href="{{ route('account.orders') }}" class="btn-heritage-secondary w-full">Riwayat Pemesanan Saya</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 text-center text-sm text-[#8B2E1F] border border-[#E3D9C9]">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-heritage-secondary w-full">Masuk / Daftar Akun</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Status / Error Notifications -->
    @if(session('status'))
        <div role="status" class="max-w-[1200px] w-full mx-auto px-4 sm:px-6 mt-6 no-print">
            <div class="border border-[#4A6B3A] bg-[#4A6B3A]/10 text-[#2A211A] px-4 py-3 flex items-start justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#4A6B3A] block">Informasi Sistem</span>
                    <p class="text-sm mt-0.5">{{ session('status') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <!-- Editorial Heritage Footer -->
    <footer class="relative overflow-hidden border-t border-[#E3D9C9] bg-[#FAF6EE] mt-20 no-print">
        <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6 py-12">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-10 border-b border-[#E3D9C9]">
                <div class="md:col-span-5 space-y-3">
                    <p class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Arsip & Edukasi Sejarah</p>
                    <h2 class="font-serif text-2xl font-bold text-[#6B3F1E]">PT Pabrik Gula Candi Baru</h2>
                    <p class="text-sm text-[#6E635A] max-w-[54ch] leading-relaxed">
                        Didirikan tahun <span class="tabular-nums font-semibold text-[#2A211A]">1911</span> di Sidoarjo, Jawa Timur. GulaTour merupakan layanan resmi kunjungan edukasi sejarah industri gula, observasi mesin uap stasiun giling, dan pemesanan e-ticket terpadu.
                    </p>
                </div>
                <div class="md:col-span-3 space-y-2 text-sm">
                    <p class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold mb-3">Navigasi</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('profile') }}" class="text-[#2A211A] hover:text-[#6B3F1E] underline-offset-4 hover:underline transition-colors">Sejarah & Visi Misi</a></li>
                        <li><a href="{{ route('events.index') }}" class="text-[#2A211A] hover:text-[#6B3F1E] underline-offset-4 hover:underline transition-colors">Katalog Event & Sesi</a></li>
                        <li><a href="{{ route('orders.lookup') }}" class="text-[#2A211A] hover:text-[#6B3F1E] underline-offset-4 hover:underline transition-colors">Cek Status Pemesanan</a></li>
                        <li><a href="{{ route('login') }}" class="text-[#2A211A] hover:text-[#6B3F1E] underline-offset-4 hover:underline transition-colors">Portal Pengunjung & Admin</a></li>
                    </ul>
                </div>
                <div class="md:col-span-4 space-y-2 text-sm text-[#6E635A]">
                    <p class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold mb-3">Kontak Operasional</p>
                    <p class="text-[#2A211A] font-medium">Jl. Raya Candi No. 10, Kec. Candi, Kabupaten Sidoarjo, Jawa Timur 61271</p>
                    <p>Telepon: <span class="tabular-nums text-[#2A211A]">(031) 8921002</span></p>
                    <p>Surel: <span class="text-[#2A211A]">info@pgcandibaru.co.id</span></p>
                    <p class="text-xs pt-2 text-[#6E635A]">Pembayaran daring diamankan melalui gerbang pembayaran Midtrans (Virtual Account, E-Wallet, Kartu Kredit).</p>
                </div>
            </div>
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-[#6E635A] gap-2">
                <p>&copy; {{ date('Y') }} PT Pabrik Gula Candi Baru — GulaTour Heritage Platform.</p>
                <p>Kunjungan Edukasi Sejarah &bull; Pemesanan Terjadwal &bull; E-Ticket QR Resmi</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('mobile-menu-toggle');
            const drawer = document.getElementById('mobile-navigation-drawer');
            if (btn && drawer) {
                btn.addEventListener('click', function () {
                    const isHidden = drawer.classList.contains('hidden');
                    drawer.classList.toggle('hidden', !isHidden);
                    btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

