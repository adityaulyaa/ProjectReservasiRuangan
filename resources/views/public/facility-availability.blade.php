@extends('layouts.public')

@section('title', 'Ketersediaan ' . $facility->name . ' - AksesRuang')

@section('content')
<div class="relative min-h-screen flex flex-col px-4 py-10 sm:px-6 lg:px-8">
    <div class="relative z-10 mx-auto w-full max-w-6xl">
        <!-- Back Link -->
        <div class="mb-6 animate-fade-in-up stagger-1" style="animation-fill-mode: both;">
            <a href="{{ route('facilities.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-amber-100/80 hover:text-teal-300 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar Fasilitas
            </a>
        </div>

        <!-- Facility Info Card -->
        <div class="mb-8 overflow-hidden rounded-3xl border border-amber-200/40 bg-black/60 shadow-2xl backdrop-blur-md animate-fade-in-up stagger-2" style="animation-fill-mode: both;">
            <div class="grid grid-cols-1 md:grid-cols-3">
                <!-- Facility Image -->
                <div class="relative h-64 md:h-auto overflow-hidden bg-slate-950">
                    <img 
                        src="{{ $facility->image_url ?? Vite::asset('resources/images/register-bg.jpg') }}" 
                        alt="{{ $facility->name }}"
                        class="h-full w-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent md:hidden"></div>
                </div>

                <!-- Facility Details -->
                <div class="col-span-2 flex flex-col justify-between p-6 sm:p-8">
                    <div>
                        <!-- Badges -->
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            @if(!empty($facility->type))
                                <span class="rounded-md border border-white/20 bg-white/10 px-2.5 py-1 text-xs font-semibold text-amber-100">
                                    {{ $facility->type }}
                                </span>
                            @endif

                            @if($isMaintenance)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-400/40 bg-amber-500/20 px-3 py-1 text-xs font-semibold text-amber-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                    Dalam Perbaikan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-teal-400/40 bg-teal-500/20 px-3 py-1 text-xs font-semibold text-teal-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-teal-400"></span>
                                    Aktif &amp; Tersedia
                                </span>
                            @endif
                        </div>

                        <!-- Name -->
                        <h1 class="mb-3 text-2xl font-black text-white sm:text-3xl">
                            {{ $facility->name }}
                        </h1>

                        <!-- Location & Capacity -->
                        <div class="mb-4 flex flex-wrap items-center gap-4 text-xs sm:text-sm text-white/80">
                            @if(!empty($facility->location))
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>{{ $facility->location }}</span>
                                </div>
                            @endif

                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Kapasitas: <strong class="text-white">{{ $facility->capacity }}</strong> orang</span>
                            </div>
                        </div>

                        <!-- Description -->
                        @if(!empty($facility->description))
                            <p class="text-xs sm:text-sm text-white/70 leading-relaxed">
                                {{ $facility->description }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Warning Banner -->
        @if($isMaintenance)
            <div class="mb-8 flex items-start gap-4 rounded-2xl border border-amber-400/50 bg-amber-500/10 p-5 backdrop-blur-md animate-fade-in-up stagger-3" style="animation-fill-mode: both;">
                <div class="rounded-lg bg-amber-500/20 p-2 text-amber-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-amber-200">Fasilitas Sedang Dalam Perbaikan</h3>
                    <p class="text-xs sm:text-sm text-white/80 mt-1">
                        Fasilitas ini sedang dalam masa pemeliharaan teknis. Seluruh slot reservasi dinonaktifkan sementara dan belum dapat dipesan hingga perbaikan selesai.
                    </p>
                </div>
            </div>
        @endif

        <!-- Date Selector & Summary Bar -->
        <div class="mb-8 rounded-2xl border border-amber-200/40 bg-black/60 p-6 backdrop-blur-md animate-fade-in-up stagger-3" style="animation-fill-mode: both;">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <!-- Date Filter Form -->
                <form method="GET" action="{{ route('facilities.availability', $facility->id) }}" class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <label for="date" class="text-xs font-semibold text-amber-100 whitespace-nowrap">
                        Pilih Tanggal:
                    </label>
                    <div class="relative">
                        <input 
                            type="date" 
                            id="date" 
                            name="date" 
                            value="{{ $date }}"
                            class="rounded-lg border border-amber-200/40 bg-slate-950/80 px-4 py-2 text-sm text-white focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition cursor-pointer"
                        >
                    </div>
                    <button 
                        type="submit" 
                        class="rounded-lg bg-teal-500 px-5 py-2 text-xs sm:text-sm font-bold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/40 btn-lift"
                    >
                        Lihat Jadwal
                    </button>

                    <!-- Quick Shortcuts -->
                    <div class="flex items-center gap-1.5 pt-2 sm:pt-0">
                        <a 
                            href="{{ route('facilities.availability', ['id' => $facility->id, 'date' => now()->toDateString()]) }}" 
                            class="rounded-md border border-white/20 bg-white/5 px-2.5 py-1 text-xs text-white/80 hover:bg-white/10 hover:text-white transition {{ $date === now()->toDateString() ? 'border-teal-400 text-teal-300' : '' }}"
                        >
                            Hari Ini
                        </a>
                        <a 
                            href="{{ route('facilities.availability', ['id' => $facility->id, 'date' => now()->addDay()->toDateString()]) }}" 
                            class="rounded-md border border-white/20 bg-white/5 px-2.5 py-1 text-xs text-white/80 hover:bg-white/10 hover:text-white transition {{ $date === now()->addDay()->toDateString() ? 'border-teal-400 text-teal-300' : '' }}"
                        >
                            Besok
                        </a>
                    </div>
                </form>

                <!-- Legend & Status Indicators -->
                <div class="flex flex-wrap items-center gap-4 text-xs font-medium border-t border-white/10 pt-4 lg:border-t-0 lg:pt-0">
                    <div class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-md bg-teal-500/80 border border-teal-300/40"></span>
                        <span class="text-white/80">Tersedia ({{ $availableCount }})</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-md bg-red-500/80 border border-red-300/40"></span>
                        <span class="text-white/80">Terbooking ({{ $bookedCount }})</span>
                    </div>
                    @if($isMaintenance)
                        <div class="flex items-center gap-1.5">
                            <span class="h-3 w-3 rounded-md bg-amber-500/80 border border-amber-300/40"></span>
                            <span class="text-amber-200">Dalam Perbaikan</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Date Label Info -->
            <div class="mt-4 pt-4 border-t border-white/10 flex items-center justify-between text-xs text-amber-100/90">
                <span>
                    Jadwal Operasional: <strong>07.00 – 20.00 WIB</strong> (Slot 30 Menit)
                </span>
                <span class="text-white/60">
                    Tanggal terpilih: <strong class="text-amber-100">{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong>
                </span>
            </div>
        </div>

        <!-- Time Slot Grid (SRS-08 Requirement) -->
        <div class="mb-10 animate-fade-in-up stagger-4" style="animation-fill-mode: both;">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-7 gap-3">
                @foreach($slotStatuses as $slot)
                    @php
                        if ($isMaintenance) {
                            $slotBg = 'border-amber-400/30 bg-amber-500/10 text-amber-200/80';
                            $badgeColor = 'text-amber-300 bg-amber-400/10 border-amber-400/30';
                            $statusText = 'Perbaikan';
                        } elseif ($slot['is_occupied']) {
                            $slotBg = 'border-red-500/30 bg-red-950/40 text-red-200';
                            $badgeColor = 'text-red-300 bg-red-500/20 border-red-400/30';
                            $statusText = 'Terbooking';
                        } else {
                            $slotBg = 'border-teal-400/30 bg-teal-950/30 text-teal-100 hover:border-teal-300 hover:bg-teal-900/40';
                            $badgeColor = 'text-teal-300 bg-teal-500/20 border-teal-400/30';
                            $statusText = 'Tersedia';
                        }
                    @endphp

                    <div class="flex flex-col justify-between rounded-xl border p-3.5 backdrop-blur-sm transition {{ $slotBg }}">
                        <!-- Time Range -->
                        <div class="text-xs font-bold text-white mb-2">
                            {{ $slot['start'] }} – {{ $slot['end'] }}
                        </div>

                        <!-- Status Label (Tanpa detail pemohon/tujuan - Privasi WCAG & SRS) -->
                        <div class="mt-auto">
                            <span class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[11px] font-semibold {{ $badgeColor }}">
                                @if(!$isMaintenance && !$slot['is_occupied'])
                                    <span class="h-1.5 w-1.5 rounded-full bg-teal-400"></span>
                                @elseif($slot['is_occupied'])
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                @else
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                                @endif
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Reservation CTA Box -->
        <div class="rounded-3xl border border-amber-200/40 bg-black/60 p-6 sm:p-8 text-center backdrop-blur-md animate-fade-in-up stagger-5" style="animation-fill-mode: both;">
            @if($isMaintenance)
                <h3 class="text-lg font-bold text-amber-200 mb-2">Fasilitas Tidak Dapat Dipesan</h3>
                <p class="text-xs sm:text-sm text-white/70 max-w-xl mx-auto mb-4">
                    Mohon maaf, saat ini fasilitas sedang dalam proses pemeliharaan. Silakan cek fasilitas lain yang tersedia atau hubungi petugas.
                </p>
                <a href="{{ route('facilities.index') }}" class="inline-flex items-center justify-center rounded-lg border border-amber-200/60 px-6 py-2.5 text-xs sm:text-sm font-semibold text-amber-100 hover:bg-white/10 transition">
                    Cari Fasilitas Lain
                </a>
            @else
                <h3 class="text-lg sm:text-xl font-bold text-white mb-2">Ingin Menggunakan Fasilitas Ini?</h3>
                <p class="text-xs sm:text-sm text-white/70 max-w-xl mx-auto mb-6">
                    Lihat slot waktu yang masih tersedia di atas, lalu ajukan reservasi untuk keperluan studi, rapat, atau kegiatan Anda.
                </p>

                @auth
                    @if(auth()->user()->role === 'user')
                        <a 
                            href="{{ route('reservations.create', ['facility_id' => $facility->id, 'date' => $date]) }}" 
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-500 px-8 py-3 text-sm font-bold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/50 btn-lift shadow-lg shadow-teal-500/20"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Ajukan Reservasi Sekarang
                        </a>
                    @else
                        <a 
                            href="{{ route('dashboard') }}" 
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-500 px-8 py-3 text-sm font-bold text-white transition hover:bg-teal-400 btn-lift"
                        >
                            Buka Dashboard {{ ucfirst(auth()->user()->role) }}
                        </a>
                    @endif
                @else
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a 
                            href="{{ route('login') }}" 
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-teal-500 px-8 py-3 text-sm font-bold text-white transition hover:bg-teal-400 btn-lift shadow-lg shadow-teal-500/20"
                        >
                            Masuk untuk Mengajukan Reservasi
                        </a>
                        <a 
                            href="{{ route('register') }}" 
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-amber-200/60 px-6 py-3 text-sm font-semibold text-amber-100 hover:bg-white/10 transition"
                        >
                            Daftar Akun Baru
                        </a>
                    </div>
                @endauth
            @endif
        </div>
    </div>
</div>
@endsection
