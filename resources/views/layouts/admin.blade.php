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
<body class="min-h-screen bg-[#FAF6EE] text-[#2A211A] antialiased">
    <a href="#admin-main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:bg-[#C8A24A] focus:text-[#2A211A] focus:px-4 focus:py-2 focus:font-semibold">
        Lewati ke konten admin
    </a>

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Fixed Sidebar on Desktop (≥ 1024px per DESIGN.md §10) -->
        <aside class="w-full lg:w-64 lg:fixed lg:inset-y-0 lg:left-0 bg-[#FAF6EE] border-b lg:border-b-0 lg:border-r border-[#E3D9C9] flex flex-col justify-between z-30">
            <div>
                <div class="p-5 border-b border-[#E3D9C9] flex items-center justify-between lg:block">
                    <div>
                        <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl font-bold text-[#6B3F1E] block">
                            GulaTour Admin
                        </a>
                        <span class="text-xs uppercase tracking-[0.06em] text-[#A67C52] font-semibold">
                            PT PG Candi Baru
                        </span>
                    </div>
                    <a href="{{ route('home') }}" class="lg:hidden text-xs font-medium text-[#6B3F1E] underline">
                        Lihat Website &rarr;
                    </a>
                </div>

                <nav aria-label="Menu Admin" class="p-3 flex lg:flex-col gap-1 overflow-x-auto">
                    <a href="{{ route('admin.dashboard') }}"
                       class="px-3.5 py-2.5 text-sm font-medium rounded-[2px] whitespace-nowrap {{ request()->routeIs('admin.dashboard') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        Ringkasan & Kuota
                    </a>
                    <a href="{{ route('admin.transactions.index') }}"
                       class="px-3.5 py-2.5 text-sm font-medium rounded-[2px] whitespace-nowrap {{ request()->routeIs('admin.transactions.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        Laporan & Data Transaksi
                    </a>
                    <a href="{{ route('admin.orders.index') }}"
                       class="px-3.5 py-2.5 text-sm font-medium rounded-[2px] whitespace-nowrap {{ request()->routeIs('admin.orders.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        Daftar Pemesanan
                    </a>
                    <a href="{{ route('admin.events.index') }}"
                       class="px-3.5 py-2.5 text-sm font-medium rounded-[2px] whitespace-nowrap {{ request()->routeIs('admin.events.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        Event, Jadwal & Harga
                    </a>
                    <a href="{{ route('admin.tickets.scanner') }}"
                       class="px-3.5 py-2.5 text-sm font-medium rounded-[2px] whitespace-nowrap {{ request()->routeIs('admin.tickets.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        Validasi QR E-Ticket
                    </a>
                    <a href="{{ route('admin.profile.edit') }}"
                       class="px-3.5 py-2.5 text-sm font-medium rounded-[2px] whitespace-nowrap {{ request()->routeIs('admin.profile.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        Profil & Galeri Pabrik
                    </a>
                    <a href="{{ route('admin.articles.index') }}"
                       class="px-3.5 py-2.5 text-sm font-medium rounded-[2px] whitespace-nowrap {{ request()->routeIs('admin.articles.*') ? 'bg-[#F3EAD8] text-[#6B3F1E] font-semibold border-l-2 border-[#6B3F1E]' : 'text-[#2A211A] hover:bg-[#F3EAD8]/60' }}">
                        Artikel & Publikasi
                    </a>
                </nav>
            </div>

            <div class="hidden lg:block p-4 border-t border-[#E3D9C9] space-y-3 text-xs">
                <div class="text-[#6E635A]">
                    Masuk sebagai:<br>
                    <strong class="text-[#2A211A] text-sm">{{ auth()->user()?->name }}</strong>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-[#E3D9C9]">
                    <a href="{{ route('home') }}" class="text-[#6B3F1E] font-semibold hover:underline">
                        &larr; Halaman Publik
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

        <!-- Main Workspace -->
        <div class="flex-1 lg:pl-64 flex flex-col min-w-0">
            <header class="border-b border-[#E3D9C9] bg-[#FFFFFF] px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="font-serif text-2xl font-bold text-[#2A211A]">@yield('page_title', 'Dashboard Operasional')</h1>
                    @hasSection('page_subtitle')
                        <p class="text-xs text-[#6E635A] mt-0.5">@yield('page_subtitle')</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @yield('header_actions')
                </div>
            </header>

            @if(session('status'))
                <div class="px-6 pt-5">
                    <div role="status" class="border border-[#4A6B3A] bg-[#4A6B3A]/10 text-[#2A211A] px-4 py-3 text-sm">
                        <strong class="text-[#4A6B3A] uppercase text-xs tracking-wider block">Berhasil</strong>
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="px-6 pt-5">
                    <div role="alert" class="border border-[#8B2E1F] bg-[#8B2E1F]/10 text-[#2A211A] px-4 py-3 text-sm">
                        <strong class="text-[#8B2E1F] uppercase text-xs tracking-wider block mb-1">Perhatian Validasi</strong>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <main id="admin-main" class="p-6 flex-1">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

