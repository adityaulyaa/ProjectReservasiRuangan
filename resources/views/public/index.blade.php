@extends('layouts.public')

@section('title', 'Beranda - AksesRuang')

@section('content')
<!-- Hero Section -->
<div class="relative flex flex-col items-center justify-center px-4 py-20 sm:px-6 lg:px-8">
    <div class="relative z-10 w-full max-w-5xl text-center animate-fade-in-up stagger-1" style="animation-fill-mode: both;">
        <!-- Heading -->
        <h1 class="mb-6 text-5xl font-bold leading-tight text-amber-100 sm:text-6xl lg:text-7xl" style="font-family: serif;">
            Temukan Ruang Terbaik<br>
            untuk Studi dan Kolaborasi
        </h1>

        <!-- Subheading -->
        <p class="mb-10 text-lg text-white/90 sm:text-xl animate-fade-in-up stagger-2" style="animation-fill-mode: both;">
            Sewa dan pesan ruang rapat & studi dengan mudah dan cepat.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-center animate-fade-in-up stagger-3" style="animation-fill-mode: both;">
            <a href="{{ route('facilities.index') }}" class="rounded-lg border-2 border-amber-200/60 px-10 py-3.5 text-base font-semibold text-amber-100 transition hover:bg-amber-200/10 hover:border-amber-200 btn-lift">
                Cari Fasilitas Sekarang
            </a>
            <a href="{{ route('facilities.index') }}" class="rounded-lg bg-teal-500 px-10 py-3.5 text-base font-semibold text-white transition hover:bg-teal-600 btn-lift">
                Cari Sekarang
            </a>
        </div>
    </div>
</div>

<!-- Featured Facilities Section -->
<div class="relative px-4 pb-20 sm:px-6 lg:px-8">
    <div class="relative z-10 mx-auto w-full max-w-7xl rounded-3xl border border-white/20 bg-black/40 p-8 backdrop-blur-md sm:p-12 glass-shimmer animate-fade-in-up stagger-4" style="animation-fill-mode: both;">
    <!-- <div class="relative z-10 mx-auto w-full max-w-7xl rounded-3xl border border-white/20 bg-black/40 p-8 backdrop-blur-md sm:p-12 animate-fade-in-up stagger-4" style="animation-fill-mode: both;"> -->
        <h2 class="mb-10 text-left text-3xl font-bold text-white sm:text-4xl animate-fade-in-up stagger-5" style="animation-fill-mode: both;">
            Fasilitas Unggulan
        </h2>

        @if($facilities->count() > 0)
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($facilities as $index => $facility)
                    @php
                        $stagger = (($index % 3) + 6);
                    @endphp
                    <x-public.facility-card :facility="$facility" :show-amenities="false" :stagger="$stagger" />
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-white/70">Belum ada fasilitas yang tersedia saat ini.</p>
            </div>
        @endif
    </div>
</div>
@endsection
