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
        'active' => 'bg-teal-600 text-white border-teal-600/40',
        'maintenance' => 'bg-amber-600 text-white border-amber-400/40',
        'inactive' => 'bg-red-600 text-white border-red-400/40',
    ];

    $statusValue = is_object($facility->status) ? $facility->status->value : $facility->status;
    $image = $facility->image_url ?? Vite::asset('resources/images/register-bg.jpg');
    $staggerClass = 'stagger-' . min(max((int)$stagger, 1), 8);
@endphp

<div class="group relative overflow-hidden rounded-2xl border border-gray-400/50 bg-black/40 shadow-2xl backdrop-blur-md transition duration-300 hover:border-gray-300/70 animate-fade-in-up {{ $staggerClass }}" style="animation-fill-mode: both;">
    <!-- Image Container -->
    <div class="relative overflow-hidden rounded-2xl">
        <img src="{{ $image }}" alt="{{ $facility->name }}" class="h-56 w-full object-cover transition duration-500 group-hover:scale-110">
        <!-- Status Badge -->
        <span class="absolute top-2 right-2 z-10 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold border {{ $statusClasses[$statusValue] ?? 'bg-gray-600 text-white border-gray-600/40' }}">
            {{ $statusLabels[$statusValue] ?? $statusValue }}
        </span>
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
    @auth
        <a href="{{ route('facilities.availability', $facility->id) }}" class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-600 btn-lift">
            Detail
        </a>
    @else
        <button onclick="showLoginToast()" class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 btn-lift">
            Detail
        </button>
    @endauth
    </div>
</div>
