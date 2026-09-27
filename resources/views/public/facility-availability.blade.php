@extends('layouts.public')

@section('lightTheme', 'true')

@section('title', 'Ketersediaan ' . $facility->name . ' - AksesRuang')

@section('content')
<div 
    class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto"
    x-data="{
        selectedSlots: {{ json_encode(
            !$isMaintenance && collect($slotStatuses)->whereIn('start', ['09:00', '09:30'])->where('is_occupied', false)->count() === 2
                ? ['09:00', '09:30']
                : collect($slotStatuses)->where('is_occupied', false)->take(1)->pluck('start')->values()->all()
        ) }},
        toggleSlot(slotStart) {
            if (this.selectedSlots.includes(slotStart)) {
                this.selectedSlots = this.selectedSlots.filter(s => s !== slotStart);
            } else {
                this.selectedSlots.push(slotStart);
                this.selectedSlots.sort();
            }
        },
        isSelected(slotStart) {
            return this.selectedSlots.includes(slotStart);
        },
        get timeDisplay() {
            if (this.selectedSlots.length === 0) return '-';
            return [...this.selectedSlots].sort().join(', ');
        },
        get durationDisplay() {
            if (this.selectedSlots.length === 0) return '-';
            return (this.selectedSlots.length * 30) + ' menit';
        },
        get reservationUrl() {
            if (this.selectedSlots.length === 0) return '#';
            const slotsParam = encodeURIComponent(this.selectedSlots.sort().join(','));
            return '{{ route('reservations.create') }}?facility_id={{ $facility->id }}&date={{ $date }}&slots=' + slotsParam;
        }
    }"
>
    <!-- Top Facility Showcase Card -->
    <div class="mb-8 rounded-3xl bg-white p-6 sm:p-8 border border-gray-100 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Facility Image -->
            <div class="lg:col-span-6 overflow-hidden rounded-2xl bg-gray-100">
                <img 
                    src="{{ $facility->image_url ?? Vite::asset('resources/images/register-bg.jpg') }}" 
                    alt="{{ $facility->name }}"
                    class="w-full h-64 sm:h-80 md:h-[340px] object-cover rounded-2xl"
                >
            </div>

            <!-- Facility Details -->
            <div class="lg:col-span-6 flex flex-col justify-center">
                <!-- Badges -->
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">
                        {{ $facility->type ?? 'Ruang Kolaborasi' }}
                    </span>

                    @if($isMaintenance)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-medium border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Dalam Perbaikan
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#EAF5EF] text-[#1E4D40] text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#1E4D40]"></span>
                            Tersedia
                        </span>
                    @endif
                </div>

                <!-- Facility Title -->
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 tracking-tight">
                    {{ $facility->name }}
                </h1>

                <!-- Facility Description -->
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                    {{ $facility->description ?? 'Ruang modern yang dirancang untuk fokus, kolaborasi, dan pertemuan produktif. Dilengkapi layar presentasi, Wi-Fi, dan pendingin ruangan.' }}
                </p>

                <!-- Facility Metadata Row -->
                <div class="mt-6 flex flex-wrap items-center gap-6 text-xs sm:text-sm text-gray-600">
                    <!-- Location -->
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#1E4D40]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ $facility->location ?? 'Gedung Kreatif' }}</span>
                    </div>

                    <!-- Capacity -->
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#1E4D40]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Hingga {{ $facility->capacity }} orang</span>
                    </div>

                    <!-- Operational Hours -->
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#1E4D40]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>07.00 - 20.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Notice Alert -->
    @if($isMaintenance)
        <div class="mb-8 flex items-start gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <div class="rounded-lg bg-amber-100 p-2 text-amber-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-amber-800">Fasilitas Sedang Dalam Perbaikan</h3>
                <p class="text-xs sm:text-sm text-amber-700 mt-1">
                    Fasilitas ini sedang dalam masa pemeliharaan teknis. Seluruh slot reservasi dinonaktifkan sementara dan belum dapat dipesan hingga perbaikan selesai.
                </p>
            </div>
        </div>
    @endif

    <!-- Bottom Section: Slot Picker & Summary Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left: Slot Picker Card -->
        <div class="lg:col-span-8 rounded-3xl bg-white p-6 sm:p-8 border border-gray-100 shadow-sm">
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
                        Pilih waktu reservasi
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-400 mt-1">
                        {{ \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y') }} · Durasi per slot 30 menit
                    </p>
                </div>

                <!-- Date Picker Input -->
                <div class="relative flex items-center">
                    <input 
                        type="date" 
                        id="datePicker" 
                        name="date" 
                        value="{{ $date }}"
                        onchange="window.location.href = '{{ route('facilities.availability', $facility->id) }}?date=' + this.value"
                        class="rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs sm:text-sm font-medium text-gray-700 shadow-xs hover:border-gray-300 focus:border-[#1E4D40] focus:outline-none focus:ring-1 focus:ring-[#1E4D40] cursor-pointer"
                    >
                </div>
            </div>

            <!-- Legend Row -->
            <div class="mt-6 mb-6 flex flex-wrap items-center gap-5 text-xs text-gray-500 border-t border-gray-100 pt-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full border border-gray-300 bg-white"></span>
                    <span>Tersedia</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#1E4D40]"></span>
                    <span>Dipilih</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                    <span>Terisi</span>
                </div>
            </div>

            <!-- Slot Grid (26 slots) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                @foreach($slotStatuses as $slot)
                    @if($isMaintenance)
                        <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-3 text-center cursor-not-allowed">
                            <div class="text-sm font-bold text-amber-800">{{ $slot['start'] }}</div>
                            <div class="text-[11px] font-medium text-amber-600 mt-1">Perbaikan</div>
                        </div>
                    @elseif($slot['is_occupied'])
                        <div class="rounded-xl border border-gray-100 bg-gray-100/90 p-3 text-center cursor-not-allowed">
                            <div class="text-sm font-bold text-gray-400">{{ $slot['start'] }}</div>
                            <div class="text-[11px] font-medium text-gray-400 mt-1">
                                Terisi <span class="sr-only">Terbooking</span>
                            </div>
                        </div>
                    @else
                        <button 
                            type="button"
                            @click="toggleSlot('{{ $slot['start'] }}')"
                            class="rounded-xl p-3 text-center transition cursor-pointer w-full focus:outline-none"
                            :class="isSelected('{{ $slot['start'] }}') 
                                ? 'bg-[#1E4D40] border border-[#1E4D40] text-white shadow-xs' 
                                : 'bg-white border border-gray-200 text-gray-800 hover:border-[#1E4D40] hover:shadow-xs'"
                        >
                            <div 
                                class="text-sm font-bold"
                                :class="isSelected('{{ $slot['start'] }}') ? 'text-white' : 'text-gray-800'"
                            >
                                {{ $slot['start'] }}
                            </div>
                            <div 
                                class="text-[11px] font-medium mt-1"
                                :class="isSelected('{{ $slot['start'] }}') ? 'text-white/80' : 'text-gray-400'"
                                x-text="isSelected('{{ $slot['start'] }}') ? 'Dipilih' : 'Tersedia'"
                            >
                                Tersedia
                            </div>
                        </button>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Right: Summary Sidebar Card -->
        <div class="lg:col-span-4 rounded-3xl bg-white p-6 sm:p-7 border border-gray-100 shadow-sm sticky top-6">
            <!-- Header -->
            <div class="text-[11px] font-bold text-[#1E4D40] tracking-wider uppercase">
                RINGKASAN RESERVASI
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-1 mb-6">
                {{ $facility->name }}
            </h3>

            <!-- Details List -->
            <div class="space-y-4 text-xs sm:text-sm">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400 font-medium">Tanggal</span>
                    <span class="font-semibold text-gray-900">
                        {{ \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('d M Y') }}
                    </span>
                </div>

                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400 font-medium">Waktu</span>
                    <span class="font-semibold text-gray-900" x-text="timeDisplay">
                        -
                    </span>
                </div>

                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-400 font-medium">Durasi</span>
                    <span class="font-semibold text-gray-900" x-text="durationDisplay">
                        -
                    </span>
                </div>
            </div>

            <!-- Cancellation Policy Notice -->
            <div class="my-6 flex items-start gap-2.5 rounded-xl bg-[#EAF5EF] p-4 text-xs text-[#1E4D40] font-medium leading-relaxed">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>Reservasi dapat dibatalkan hingga 2 jam sebelum jadwal.</span>
            </div>

            <!-- Action Button -->
            @if($isMaintenance)
                <button 
                    disabled 
                    class="w-full py-3.5 px-4 rounded-xl bg-gray-200 text-gray-400 text-sm font-semibold cursor-not-allowed"
                >
                    Fasilitas Tidak Dapat Dipesan
                </button>
            @else
                @auth
                    @if(auth()->user()->role === 'user')
                        <a 
                            :href="reservationUrl"
                            class="w-full block py-3.5 px-4 rounded-xl bg-[#1E4D40] hover:bg-[#163c32] text-white text-sm font-semibold transition text-center shadow-xs cursor-pointer"
                            :class="{ 'opacity-50 pointer-events-none': selectedSlots.length === 0 }"
                        >
                            Lanjutkan reservasi
                        </a>
                    @else
                        <a 
                            href="{{ route('dashboard') }}" 
                            class="w-full block py-3.5 px-4 rounded-xl bg-[#1E4D40] hover:bg-[#163c32] text-white text-sm font-semibold transition text-center shadow-xs"
                        >
                            Buka Dashboard {{ ucfirst(auth()->user()->role) }}
                        </a>
                    @endif
                @else
                    <button 
                        type="button"
                        onclick="window.showLoginToast()"
                        class="w-full block py-3.5 px-4 rounded-xl bg-[#1E4D40] hover:bg-[#163c32] text-white text-sm font-semibold transition text-center shadow-xs cursor-pointer"
                    >
                        Lanjutkan reservasi
                    </button>
                @endauth
            @endif
        </div>
    </div>
</div>
@endsection
