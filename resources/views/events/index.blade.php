@extends('layouts.app')

@section('title', 'Katalog Event & Jadwal Tour — GulaTour PG Candi Baru')

@section('content')
    <section class="relative overflow-hidden border-b border-[#E3D9C9] py-12">
        <div class="relative z-10 max-w-[1200px] mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div data-reveal="left">
                    <span class="text-xs uppercase tracking-[0.08em] text-[#A67C52] font-semibold">Jadwal Tur Resmi &bull; Edukasi Pabrik Gula</span>
                    <h1 class="font-serif text-4xl font-bold text-[#2A211A] mt-1">Daftar Event Tour & Jadwal Sesi</h1>
                </div>
                <p data-reveal="right" class="text-sm text-[#6E635A] max-w-[48ch]">
                    Harga tiket berlaku seragam per peserta. Pilih event di bawah ini untuk melihat jadwal sesi dan sisa kuota secara langsung.
                </p>
            </div>
        </div>
    </section>

    <section class="py-12">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
            @if($events->isEmpty())
                <!-- Empty State per DESIGN.md §11 -->
                <div data-reveal="scale" class="border border-[#E3D9C9] bg-[#FFFFFF] p-12 text-center space-y-4">
                    <h2 class="font-serif text-2xl text-[#2A211A]">Belum ada event tour aktif.</h2>
                    <p class="text-sm text-[#6E635A]">Event akan tampil setelah admin mengaktifkan jadwal.</p>
                    <div>
                        <a href="{{ route('profile') }}" class="btn-heritage-secondary">Lihat Profil Perusahaan</a>
                    </div>
                </div>
            @else
                <!-- Editorial List Layout (No 3-Card Grid per DESIGN.md §6 & §7) -->
                <div data-reveal-stagger="130" class="divide-y divide-[#E3D9C9] border-t border-b border-[#E3D9C9]">
                    @foreach($events as $event)
                        @php
                            $upcomingSchedules = $event->schedules->filter(fn($s) => !$s->isPast());
                            $availableSchedules = $event->schedules->filter(fn($s) => $s->isAvailable());
                            $totalRemaining = $availableSchedules->sum(fn($s) => $s->remainingQuota());
                        @endphp
                        <article data-reveal="up" class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            <div class="lg:col-span-3 heritage-3d-stage">
                                <div class="heritage-stack-group">
                                    <div class="heritage-stack-plate-2" aria-hidden="true"></div>
                                    <div class="heritage-stack-plate-1" aria-hidden="true"></div>
                                    <div data-tilt-card data-tilt-max="6" class="heritage-card-interactive relative z-10 aspect-[4/3] bg-[#F3EAD8] border border-[#E3D9C9] p-4 flex flex-col justify-between">
                                        <div class="depth-z-mid flex items-center justify-between text-xs text-[#6B3F1E] font-semibold uppercase">
                                            <span>Tur Heritage</span>
                                            <span class="tabular-nums"><span data-count-up="{{ $event->duration_minutes }}">{{ $event->duration_minutes }}</span> Menit</span>
                                        </div>
                                        <p class="depth-z-front font-serif text-lg font-bold text-[#2A211A] leading-snug">
                                            {{ $event->title }}
                                        </p>
                                        <span class="depth-z-mid text-xs text-[#6E635A] border-t border-[#E3D9C9] pt-2">
                                            {{ $event->meeting_point }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-6 space-y-4">
                                <div class="flex flex-wrap items-center gap-3 text-xs">
                                    <span class="font-semibold uppercase tracking-wider text-[#4A6B3A]">Aktif</span>
                                    <span class="text-[#E3D9C9]">|</span>
                                    <span class="tabular-nums text-[#2A211A]">{{ $availableSchedules->count() }} dari {{ $upcomingSchedules->count() }} Sesi Tersedia</span>
                                    <span class="text-[#E3D9C9]">|</span>
                                    <span class="tabular-nums font-semibold {{ $totalRemaining > 0 ? 'text-[#4A6B3A]' : 'text-[#8B2E1F]' }}">
                                        Sisa Kuota Total: <span data-count-up="{{ $totalRemaining }}">{{ $totalRemaining }}</span> Tiket
                                    </span>
                                </div>

                                <h2 class="font-serif text-2xl font-bold text-[#2A211A]">
                                    <a href="{{ route('events.show', $event->slug) }}" class="hover:text-[#6B3F1E] transition-colors">
                                        {{ $event->title }}
                                    </a>
                                </h2>

                                <p class="text-sm text-[#6E635A] leading-relaxed">
                                    {{ $event->description }}
                                </p>

                                @if(!empty($event->highlights))
                                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1 text-xs text-[#2A211A]">
                                        @foreach($event->highlights as $highlight)
                                            <li class="flex items-start gap-2">
                                                <span class="text-[#6B3F1E] font-bold">&mdash;</span>
                                                <span>{{ $highlight }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="lg:col-span-3 lg:border-l lg:border-[#E3D9C9] lg:pl-6 flex flex-col justify-between space-y-4 heritage-3d-stage">
                                <div data-tilt-card data-tilt-max="4.5" class="heritage-card-interactive bg-[#FFFFFF] border border-[#E3D9C9] p-4">
                                    <span class="text-xs uppercase tracking-wider text-[#6E635A] block">Harga Tiket Tetap</span>
                                    <span class="depth-z-front font-serif text-2xl font-bold text-[#6B3F1E] tabular-nums block mt-1">
                                        {{ $event->formattedPrice() }}
                                    </span>
                                    <span class="text-xs text-[#6E635A] block mt-1">Tarif seragam per orang</span>
                                </div>

                                <a href="{{ route('events.show', $event->slug) }}" class="btn-heritage-primary w-full text-center">
                                    Pilih Jadwal & Pesan
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection

