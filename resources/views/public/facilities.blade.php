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
    <div class="relative z-10 mx-auto w-full max-w-7xl mb-12 animate-fade-in-up stagger-3" style="animation-fill-mode: both;">
        <form method="GET" action="{{ route('facilities.index') }}" class="flex flex-col gap-3 rounded-2xl border border-amber-200/60 bg-black/30 p-6 backdrop-blur-md sm:flex-row sm:items-end sm:gap-3">
            <!-- Search Input -->
            <div class="flex-1">
                <label for="search" class="block text-xs font-semibold text-amber-100 mb-1">Cari nama fasilitas</label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        placeholder="Cari nama fasilitas..." 
                        value="{{ request('search') }}"
                        class="w-full rounded-lg border border-amber-200/40 bg-white/10 px-4 py-2.5 text-white placeholder-white/50 focus:border-teal-300 focus:outline-none focus:ring-2 focus:ring-teal-300/20 transition"
                    >
                    <svg class="absolute right-3 top-2.5 h-5 w-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Location Dropdown -->
            <div class="flex-1">
                <label for="location" class="block text-xs font-semibold text-amber-100 mb-1">Lokasi / Gedung</label>
                <select 
                    id="location" 
                    name="location"
                    class="w-full rounded-lg border border-amber-200/40 bg-white/10 px-4 py-2.5 text-white focus:border-teal-300 focus:outline-none focus:ring-2 focus:ring-teal-300/20 transition appearance-none"
                >
                    <option value="">Semua Lokasi</option>
                    <option value="Gedung A" {{ request('location') === 'Gedung A' ? 'selected' : '' }}>Gedung A</option>
                    <option value="Gedung B" {{ request('location') === 'Gedung B' ? 'selected' : '' }}>Gedung B</option>
                    <option value="Gedung C" {{ request('location') === 'Gedung C' ? 'selected' : '' }}>Gedung C</option>
                </select>
            </div>

            <!-- Capacity Dropdown -->
            <div class="flex-1">
                <label for="capacity" class="block text-xs font-semibold text-amber-100 mb-1">Kapasitas</label>
                <select 
                    id="capacity" 
                    name="capacity"
                    class="w-full rounded-lg border border-amber-200/40 bg-white/10 px-4 py-2.5 text-white focus:border-teal-300 focus:outline-none focus:ring-2 focus:ring-teal-300/20 transition appearance-none"
                >
                    <option value="">Semua Kapasitas</option>
                    <option value="10" {{ request('capacity') === '10' ? 'selected' : '' }}>≥ 10 orang</option>
                    <option value="25" {{ request('capacity') === '25' ? 'selected' : '' }}>≥ 25 orang</option>
                    <option value="50" {{ request('capacity') === '50' ? 'selected' : '' }}>≥ 50 orang</option>
                    <option value="100" {{ request('capacity') === '100' ? 'selected' : '' }}>≥ 100 orang</option>
                </select>
            </div>

            <!-- Search Button -->
            <button 
                type="submit"
                class="rounded-lg bg-teal-500 px-6 py-2.5 font-bold text-white transition hover:bg-teal-400 btn-lift"
            >
                Cari
            </button>
        </form>
    </div>

    <!-- Facilities Grid -->
    <div class="relative z-10 mx-auto w-full max-w-7xl">
        @if($facilities->count() > 0)
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($facilities as $index => $facility)
                    @php
                        $cardIndex = ($index % 3) + 6;
                    @endphp
                    <x-public.facility-card :facility="$facility" :show-amenities="true" :stagger="$cardIndex" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12 flex items-center justify-center gap-2 text-sm animate-fade-in-up stagger-8" style="animation-fill-mode: both;">
                <span class="text-amber-100">Halaman</span>
                
                @if($facilities->onFirstPage())
                    <span class="rounded-lg px-3 py-2 text-white/50">« Sebelumnya</span>
                @else
                    <a href="{{ $facilities->previousPageUrl() }}" class="rounded-lg border border-amber-200/60 px-3 py-2 text-amber-100 hover:bg-amber-200/10">« Sebelumnya</a>
                @endif

                @foreach($facilities->getUrlRange(1, $facilities->lastPage()) as $page => $url)
                    @if($page == $facilities->currentPage())
                        <span class="rounded-lg bg-teal-500 px-3 py-2 font-bold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="rounded-lg border border-amber-200/60 px-3 py-2 text-amber-100 hover:bg-amber-200/10">{{ $page }}</a>
                    @endif
                @endforeach

                @if($facilities->hasMorePages())
                    <a href="{{ $facilities->nextPageUrl() }}" class="rounded-lg border border-amber-200/60 px-3 py-2 text-amber-100 hover:bg-amber-200/10">Selanjutnya »</a>
                @else
                    <span class="rounded-lg px-3 py-2 text-white/50">Selanjutnya »</span>
                @endif
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
