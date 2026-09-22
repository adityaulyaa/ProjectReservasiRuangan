@props([
    'facility',
    'showAmenities' => false,
    'stagger' => 1,
])

@php
    $statusLabels = [
        'active' => 'Aktif',
        'maintenance' => 'Dalam Perbaikan',
        'inactive' => 'Tidak Aktif',
    ];

    $statusClasses = [
        'active' => 'bg-teal-500/20 text-teal-200 border-teal-400/40',
        'maintenance' => 'bg-yellow-500/20 text-yellow-200 border-yellow-400/40',
        'inactive' => 'bg-gray-500/20 text-gray-200 border-gray-400/40',
    ];

    $statusValue = is_object($facility->status) ? $facility->status->value : $facility->status;
    $image = Vite::asset('resources/images/register-bg.jpg');
    $staggerClass = 'stagger-' . min(max((int)$stagger, 1), 8);
@endphp

<div class="group relative overflow-hidden rounded-2xl border border-gray-400/50 bg-black/40 shadow-2xl backdrop-blur-md transition duration-300 hover:border-gray-300/70 animate-fade-in-up {{ $staggerClass }}" style="animation-fill-mode: both;">
    <!-- Image Container -->
    <div class="relative overflow-hidden rounded-2xl">
        <img src="{{ $image }}" alt="{{ $facility->name }}" class="h-56 w-full object-cover transition duration-500 group-hover:scale-110">
        <!-- Overlay gradient -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
    </div>

    <!-- Content Container -->
    <div class="relative -mt-20 flex flex-col justify-end p-6 pb-6 pt-24">
        <!-- Facility Name -->
        <h3 class="mb-2 text-xl font-bold text-white">{{ $facility->name }}</h3>

        <!-- Info -->
        <p class="mb-6 text-sm text-white/80">Kapasitas: {{ $facility->capacity }} orang</p>

        <!-- Detail Button -->
        <a href="{{ route('facilities.availability', $facility->id) }}" class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-600 btn-lift">
            Detail
        </a>
    </div>
</div>
