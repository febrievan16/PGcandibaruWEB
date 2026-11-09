<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Operasional — GulaTour PG Candi Baru')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FAF6EE] text-[#2A211A] antialiased overflow-x-hidden">
    <a href="#admin-main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:bg-[#C8A24A] focus:text-[#2A211A] focus:px-4 focus:py-2 focus:font-semibold">
        Lewati ke konten admin
    </a>

    <!-- ========================================================================= -->
    <!-- MOBILE SLIDE-OVER NAVIGATION DRAWER (< 1024px / lg)                       -->
    <!-- ========================================================================= -->
    <div id="admin-mobile-drawer"
         class="no-print lg:hidden fixed inset-0 z-50 hidden"
         role="dialog"
         aria-modal="true"
         aria-label="Menu Navigasi Admin">
        <!-- Backdrop -->
        <div id="admin-drawer-backdrop" class="fixed inset-0 bg-[#2A211A]/50 backdrop-blur-xs transition-opacity"></div>

        <!-- Drawer Panel -->
        <div class="fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-[#FAF6EE] border-r border-[#E3D9C9] shadow-2xl flex flex-col justify-between z-10 overflow-y-auto">
            <div>
                <div class="p-4 sm:p-5 border-b border-[#E3D9C9] flex items-center justify-between gap-3">
                    <div>
                        <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl font-bold text-[#6B3F1E] block leading-tight">
                            GulaTour Admin
                        </a>
                        <span class="text-[11px] uppercase tracking-[0.06em] text-[#A67C52] font-semibold">
                            PT PG Candi Baru 1911
                        </span>
                    </div>
                    <button type="button"
                            id="admin-drawer-close"
                            aria-label="Tutup menu navigasi"
                            class="w-10 h-10 rounded-md border border-[#E3D9C9] bg-[#FFFFFF] flex items-center justify-center text-[#6E635A] hover:text-[#2A211A] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <nav aria-label="Menu Admin Mobile" class="p-3 space-y-1">
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md min-h-[44px] {{ request()->routeIs('admin.dashboard') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>Ringkasan &amp; Kuota</span>
                    </a>
                    <a href="{{ route('admin.transactions.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md min-h-[44px] {{ request()->routeIs('admin.transactions.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Laporan &amp; Data Transaksi</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md min-h-[44px] {{ request()->routeIs('admin.orders.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <span>Daftar Pemesanan</span>
                    </a>
                    <a href="{{ route('admin.events.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md min-h-[44px] {{ request()->routeIs('admin.events.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>Event, Jadwal &amp; Harga</span>
                    </a>
                    <a href="{{ route('admin.tickets.scanner') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md min-h-[44px] {{ request()->routeIs('admin.tickets.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h.01M17 7h.01M7 17h.01M17 17h.01"/></svg>
                        <span>Validasi QR E-Ticket</span>
                    </a>
                    <a href="{{ route('admin.profile.edit') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md min-h-[44px] {{ request()->routeIs('admin.profile.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                        <span>Profil &amp; Galeri Pabrik</span>
                    </a>
                    <a href="{{ route('admin.articles.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md min-h-[44px] {{ request()->routeIs('admin.articles.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span>Artikel &amp; Publikasi</span>
                    </a>
                </nav>
            </div>

            <div class="p-4 border-t border-[#E3D9C9] space-y-3 text-xs bg-[#FAF6EE]">
                <div class="text-[#6E635A]">
                    Masuk sebagai:<br>
                    <strong class="text-[#2A211A] text-sm">{{ auth()->user()?->name }}</strong>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-[#E3D9C9]">
                    <a href="{{ route('home') }}" class="text-[#6B3F1E] font-semibold hover:underline inline-flex items-center gap-1 group">
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        <span>Halaman Publik</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[#8B2E1F] font-semibold hover:underline cursor-pointer">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- ========================================================================= -->
        <!-- DESKTOP FIXED SIDEBAR (≥ 1024px / lg per DESIGN.md §10)                   -->
        <!-- ========================================================================= -->
        <aside class="no-print hidden lg:flex lg:w-64 lg:fixed lg:inset-y-0 lg:left-0 bg-[#FAF6EE] border-r border-[#E3D9C9] flex-col justify-between z-30">
            <div>
                <div class="p-5 border-b border-[#E3D9C9]">
                    <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl font-bold text-[#6B3F1E] block">
                        GulaTour Admin
                    </a>
                    <span class="text-xs uppercase tracking-[0.06em] text-[#A67C52] font-semibold">
                        PT PG Candi Baru
                    </span>
                </div>

                <nav aria-label="Menu Admin" class="p-3 flex flex-col gap-1">
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>Ringkasan &amp; Kuota</span>
                    </a>
                    <a href="{{ route('admin.transactions.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.transactions.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Laporan &amp; Data Transaksi</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <span>Daftar Pemesanan</span>
                    </a>
                    <a href="{{ route('admin.events.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.events.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>Event, Jadwal &amp; Harga</span>
                    </a>
                    <a href="{{ route('admin.tickets.scanner') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.tickets.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h.01M17 7h.01M7 17h.01M17 17h.01"/></svg>
                        <span>Validasi QR E-Ticket</span>
                    </a>
                    <a href="{{ route('admin.profile.edit') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.profile.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                        <span>Profil &amp; Galeri Pabrik</span>
                    </a>
                    <a href="{{ route('admin.articles.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.articles.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-3 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span>Artikel &amp; Publikasi</span>
                    </a>
                </nav>
            </div>

            <div class="p-4 border-t border-[#E3D9C9] space-y-3 text-xs">
                <div class="text-[#6E635A]">
                    Masuk sebagai:<br>
                    <strong class="text-[#2A211A] text-sm">{{ auth()->user()?->name }}</strong>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-[#E3D9C9]">
                    <a href="{{ route('home') }}" class="text-[#6B3F1E] font-semibold hover:underline inline-flex items-center gap-1 group">
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        <span>Halaman Publik</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[#8B2E1F] font-medium hover:underline cursor-pointer">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ========================================================================= -->
        <!-- MAIN WORKSPACE                                                            -->
        <!-- ========================================================================= -->
        <div id="admin-workspace-wrapper" class="flex-1 lg:pl-64 print:pl-0 flex flex-col min-w-0">
            <!-- Mobile Sticky Topbar (< 1024px) -->
            <div class="no-print lg:hidden sticky top-0 z-30 bg-[#FAF6EE]/95 backdrop-blur-xs border-b border-[#E3D9C9] px-4 py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <button type="button"
                            id="admin-drawer-open"
                            aria-expanded="false"
                            aria-controls="admin-mobile-drawer"
                            aria-label="Buka menu navigasi admin"
                            class="w-10 h-10 rounded-md border border-[#E3D9C9] bg-[#FFFFFF] flex items-center justify-center text-[#6B3F1E] hover:bg-[#F3EAD8] transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <div>
                        <a href="{{ route('admin.dashboard') }}" class="font-serif text-lg font-bold text-[#6B3F1E] leading-none block">
                            GulaTour Admin
                        </a>
                        <span class="text-[10px] uppercase tracking-wider text-[#A67C52] font-semibold block mt-0.5">
                            PT PG Candi Baru
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" class="px-2.5 py-1.5 text-xs font-medium text-[#6B3F1E] border border-[#E3D9C9] bg-[#FFFFFF] rounded-md hover:bg-[#F3EAD8]">
                        Website
                    </a>
                </div>
            </div>

            <!-- Page Header & Action Bar -->
            <header class="no-print border-b border-[#E3D9C9] bg-[#FFFFFF] px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="font-serif text-xl sm:text-2xl font-bold text-[#2A211A] leading-snug">@yield('page_title', 'Dashboard Operasional')</h1>
                    @hasSection('page_subtitle')
                        <p class="text-xs text-[#6E635A] mt-0.5 leading-relaxed">@yield('page_subtitle')</p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @yield('header_actions')
                </div>
            </header>

            @if(session('status'))
                <div class="px-4 sm:px-6 pt-5 no-print">
                    <div role="status" class="rounded-lg border border-[#4A6B3A] bg-[#4A6B3A]/10 text-[#2A211A] px-4 py-3 text-sm">
                        <strong class="text-[#4A6B3A] uppercase text-xs tracking-wider block">Berhasil</strong>
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="px-4 sm:px-6 pt-5 no-print">
                    <div role="alert" class="rounded-lg border border-[#8B2E1F] bg-[#8B2E1F]/10 text-[#2A211A] px-4 py-3 text-sm">
                        <strong class="text-[#8B2E1F] uppercase text-xs tracking-wider block mb-1">Perhatian Validasi</strong>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <main id="admin-main" class="p-4 sm:p-6 print:p-0 flex-1">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const drawer = document.getElementById('admin-mobile-drawer');
            const openBtn = document.getElementById('admin-drawer-open');
            const closeBtn = document.getElementById('admin-drawer-close');
            const backdrop = document.getElementById('admin-drawer-backdrop');

            function openDrawer() {
                if (!drawer) return;
                drawer.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
            }

            function closeDrawer() {
                if (!drawer) return;
                drawer.classList.add('hidden');
                document.body.style.overflow = '';
                if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
            }

            if (openBtn) openBtn.addEventListener('click', openDrawer);
            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (backdrop) backdrop.addEventListener('click', closeDrawer);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && drawer && !drawer.classList.contains('hidden')) {
                    closeDrawer();
                }
            });
        });
    </script>
</body>
</html>

