@extends('layouts.public')

@section('title', 'Daftar Fasilitas - AksesRuang')

@section('content')
<div class="relative min-h-screen flex flex-col px-4 py-12 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="relative z-10 mx-auto w-full max-w-7xl text-center mb-12 animate-fade-in-up stagger-1" style="animation-fill-mode: both;">
        <h1 class="mb-4 text-4xl font-black text-amber-100 sm:text-5xl">
            Daftar Fasilitas Kampus
        </h1>
        <p class="text-lg text-white/80 animate-fade-in-up stagger-2" style="animation-fill-mode: both;">
            Sewa dan pesan ruang rapat &amp; studi dengan mudah dan cepat.
        </p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="relative z-10 mx-auto w-full max-w-7xl mb-8 animate-fade-in-up stagger-3" style="animation-fill-mode: both;">
        <form method="GET" action="{{ route('facilities.index') }}" class="grid grid-cols-1 gap-4 rounded-2xl border border-amber-200/40 bg-black/50 p-6 backdrop-blur-md sm:grid-cols-2 lg:grid-cols-5 sm:items-end">
            <!-- Search Input -->
            <div>
                <label for="search" class="block text-xs font-semibold text-amber-100 mb-1.5">Nama Fasilitas</label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        placeholder="Cari nama fasilitas..." 
                        value="{{ request('search') }}"
                        class="w-full rounded-lg border border-amber-200/40 bg-slate-950/70 pl-3 pr-9 py-2.5 text-sm text-white placeholder-white/50 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition"
                    >
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-4 w-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Type Dropdown -->
            <div>
                <label for="type" class="block text-xs font-semibold text-amber-100 mb-1.5">Tipe Fasilitas</label>
                <select 
                    id="type" 
                    name="type"
                    class="w-full rounded-lg border border-amber-200/40 bg-slate-950/70 px-3 py-2.5 text-sm text-white focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition [&>option]:bg-slate-900 [&>option]:text-white cursor-pointer"
                >
                    <option value="">Semua Tipe</option>
                    @if(isset($types))
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Location Dropdown -->
            <div>
                <label for="location" class="block text-xs font-semibold text-amber-100 mb-1.5">Lokasi / Gedung</label>
                <select 
                    id="location" 
                    name="location"
                    class="w-full rounded-lg border border-amber-200/40 bg-slate-950/70 px-3 py-2.5 text-sm text-white focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition [&>option]:bg-slate-900 [&>option]:text-white cursor-pointer"
                >
                    <option value="">Semua Lokasi</option>
                    @if(isset($locations))
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}" {{ request('location') === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Capacity Min Dropdown -->
            <div>
                <label for="capacity_min" class="block text-xs font-semibold text-amber-100 mb-1.5">Kapasitas Minimal</label>
                <select 
                    id="capacity_min" 
                    name="capacity_min"
                    class="w-full rounded-lg border border-amber-200/40 bg-slate-950/70 px-3 py-2.5 text-sm text-white focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition [&>option]:bg-slate-900 [&>option]:text-white cursor-pointer"
                >
                    <option value="">Semua Kapasitas</option>
                    <option value="10" {{ (request('capacity_min') == '10' || request('capacity') == '10') ? 'selected' : '' }}>≥ 10 orang</option>
                    <option value="25" {{ (request('capacity_min') == '25' || request('capacity') == '25') ? 'selected' : '' }}>≥ 25 orang</option>
                    <option value="50" {{ (request('capacity_min') == '50' || request('capacity') == '50') ? 'selected' : '' }}>≥ 50 orang</option>
                    <option value="100" {{ (request('capacity_min') == '100' || request('capacity') == '100') ? 'selected' : '' }}>≥ 100 orang</option>
                </select>
            </div>

            <!-- Action Buttons: Cari + Reset -->
            <div class="flex gap-2">
                <button 
                    type="submit"
                    class="flex-1 rounded-lg bg-teal-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/50 btn-lift"
                >
                    Cari
                </button>
                @if(request()->filled('search') || request()->filled('type') || request()->filled('location') || request()->filled('capacity_min') || request()->filled('capacity'))
                    <a 
                        href="{{ route('facilities.index') }}"
                        class="rounded-lg border border-amber-200/40 bg-white/5 px-3.5 py-2.5 text-sm font-medium text-amber-100 hover:bg-white/10 transition inline-flex items-center justify-center"
                        title="Reset filter"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Result count tag (SRS-07 requirement) -->
        <div class="mt-4 flex items-center justify-between text-xs text-amber-100/80 px-1">
            <span>Menampilkan <strong>{{ $facilities->count() }}</strong> fasilitas</span>
            @if(request()->filled('search') || request()->filled('type') || request()->filled('location') || request()->filled('capacity_min') || request()->filled('capacity'))
                <span class="text-teal-300">Filter aktif</span>
            @endif
        </div>
    </div>

    <!-- Facilities Grid -->
    <div class="relative z-10 mx-auto w-full max-w-7xl">
        @if($facilities->count() > 0)
            <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach($facilities as $index => $facility)
                    @php
                        $cardIndex = ($index % 4) + 5;
                    @endphp
                    <x-public.facility-card :facility="$facility" :show-amenities="true" :stagger="$cardIndex" />
                @endforeach
            </div>
        @else
            <div class="text-center py-12 animate-fade-in-up stagger-6" style="animation-fill-mode: both;">
                <p class="text-xl text-amber-100/70">Tidak ada fasilitas yang ditemukan.</p>
                <a href="{{ route('facilities.index') }}" class="mt-4 inline-block text-teal-300 hover:text-teal-200">Kembali ke daftar lengkap</a>
            </div>
        @endif
    </div>
</div>
@endsection
