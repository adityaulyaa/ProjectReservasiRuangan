@extends('layouts.public')

@section('title', 'Ketersediaan ' . $facility->name . ' - AksesRuang')

@section('content')
    <div class="relative min-h-screen py-8 px-4 sm:px-6 lg:px-8" x-data="{
                selectedSlots: [],
                slotEndMap: {{ json_encode(collect($slotStatuses)->pluck('end', 'start')->all()) }},
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
                    const sorted = [...this.selectedSlots].sort();
                    return sorted.map(s => s + ' - ' + this.slotEndMap[s]).join(', ');
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
            }">
        <div class="relative z-10 mx-auto w-full max-w-7xl">

            <!-- Back Link -->
            <div class="mb-6">
                <a href="{{ route('facilities.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-amber-100/80 hover:text-teal-300 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Daftar Fasilitas
                </a>
            </div>

            <!-- ===== Top Facility Showcase Card (Liquid Glass) ===== -->
            <div class="mb-8 rounded-3xl liquid-glass p-6 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <!-- Facility Image -->
                    <div class="lg:col-span-5 overflow-hidden rounded-2xl">
                        <img src="{{ $facility->image_url ?? Vite::asset('resources/images/register-bg.jpg') }}"
                            alt="{{ $facility->name }}" class="w-full h-64 sm:h-80 md:h-[340px] object-cover rounded-2xl">
                    </div>

                    <!-- Facility Details -->
                    <div class="lg:col-span-7 flex flex-col justify-center">
                        <!-- Badges -->
                        <div class="flex items-center gap-2 mb-3">
                            <span
                                class="px-3 py-1 rounded-full border border-white/20 bg-white/10 text-amber-100 text-xs font-medium backdrop-blur-sm">
                                {{ $facility->type ?? 'Ruang Kolaborasi' }}
                            </span>

                            @if($isMaintenance)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-amber-400/40 bg-amber-500/20 text-amber-200 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                    Dalam Perbaikan
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-teal-400/40 bg-teal-500/20 text-teal-200 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                                    Tersedia
                                </span>
                            @endif
                        </div>

                        <!-- Facility Title -->
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white tracking-tight">
                            {{ $facility->name }}
                        </h1>

                        <!-- Facility Description -->
                        @if(!empty($facility->description))
                            <p class="mt-3 text-sm text-white/70 leading-relaxed">
                                {{ $facility->description }}
                            </p>
                        @endif

                        <!-- Facility Metadata Row -->
                        <div class="mt-6 flex flex-wrap items-center gap-6 text-xs sm:text-sm text-white/80">
                            <!-- Location -->
                            @if(!empty($facility->location))
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>{{ $facility->location }}</span>
                                </div>
                            @endif

                            <!-- Capacity -->
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Hingga {{ $facility->capacity }} orang</span>
                            </div>

                            <!-- Operational Hours -->
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>07.00 – 20.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Maintenance Warning ===== -->
            @if($isMaintenance)
                <div class="mb-8 flex items-start gap-4 rounded-2xl border border-amber-400/50 bg-amber-500/10 p-5 backdrop-blur-md">
                    <div class="rounded-lg bg-amber-500/20 p-2 text-amber-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-amber-200">Fasilitas Sedang Dalam Perbaikan</h3>
                        <p class="text-xs sm:text-sm text-white/80 mt-1">
                            Fasilitas ini sedang dalam masa pemeliharaan teknis. Seluruh slot reservasi dinonaktifkan sementara
                            dan belum dapat dipesan hingga perbaikan selesai.
                        </p>
                    </div>
                </div>
            @endif

            <!-- ===== Bottom Section: Slot Picker & Summary ===== -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Left: Slot Picker Card (Liquid Glass) -->
                <div class="lg:col-span-8 rounded-3xl liquid-glass p-6 sm:p-8">
                    <!-- Header Row -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-xl sm:text-2xl font-bold text-white">
                                    Pilih waktu reservasi
                                </h2>
                                @guest
                                    <span class="px-2.5 py-0.5 rounded-full border border-amber-300/30 bg-amber-400/10 text-amber-200 text-xs font-medium">
                                        Mode Lihat Jadwal
                                    </span>
                                @endguest
                            </div>
                            <p class="text-xs sm:text-sm text-white/50 mt-1">
                                {{ \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y') }} ·
                                Durasi per slot 30 menit
                            </p>
                        </div>

                        <!-- Date Picker Input -->
                        <div class="relative flex items-center">
                            <input type="date" id="datePicker" name="date" value="{{ $date }}"
                                onchange="window.location.href = '{{ route('facilities.availability', $facility->id) }}?date=' + this.value"
                                class="rounded-xl border border-white/20 bg-white/10 backdrop-blur-sm px-3.5 py-2 text-xs sm:text-sm font-medium text-white hover:bg-white/15 focus:border-teal-400 focus:outline-none focus:ring-1 focus:ring-teal-400/30 cursor-pointer transition">
                        </div>
                    </div>

                    <!-- Legend Row -->
                    <div
                        class="mt-6 mb-6 flex flex-wrap items-center gap-5 text-xs text-white/60 border-t border-white/10 pt-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full border border-white/30 bg-white/10"></span>
                            <span>Tersedia</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span>
                            <span>Dipilih</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-white/15"></span>
                            <span>Terisi</span>
                        </div>
                    </div>

                    <!-- Slot Grid (26 slots, showing time range) -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @foreach($slotStatuses as $slot)
                            @if($isMaintenance)
                                {{-- Maintenance: all slots disabled --}}
                                <div class="rounded-xl liquid-glass-disabled p-3 text-center cursor-not-allowed">
                                    <div class="text-xs font-bold text-amber-200/70">{{ $slot['start'] }} – {{ $slot['end'] }}</div>
                                    <div class="text-[11px] font-medium text-amber-300/60 mt-1.5">Perbaikan</div>
                                </div>
                            @elseif($slot['is_occupied'])
                                {{-- Occupied/Booked slot --}}
                                <div class="rounded-xl liquid-glass-disabled p-3 text-center cursor-not-allowed">
                                    <div class="text-xs font-bold text-white/30">{{ $slot['start'] }} – {{ $slot['end'] }}</div>
                                    <div class="text-[11px] font-medium text-white/25 mt-1.5">
                                        Terisi <span class="sr-only">Terbooking</span>
                                    </div>
                                </div>
                            @else
                                {{-- Available slot (interactive) --}}
                                <button type="button" @click="toggleSlot('{{ $slot['start'] }}')"
                                    class="rounded-xl p-3 text-center cursor-pointer w-full focus:outline-none focus:ring-2 focus:ring-teal-400/30"
                                    :class="isSelected('{{ $slot['start'] }}') ? 'liquid-glass-selected' : 'liquid-glass-slot'">
                                    <div class="text-xs font-bold"
                                        :class="isSelected('{{ $slot['start'] }}') ? 'text-white' : 'text-white/90'">
                                        {{ $slot['start'] }} – {{ $slot['end'] }}
                                    </div>
                                    <div class="text-[11px] font-medium mt-1.5"
                                        :class="isSelected('{{ $slot['start'] }}') ? 'text-teal-200' : 'text-white/50'"
                                        x-text="isSelected('{{ $slot['start'] }}') ? 'Dipilih' : 'Tersedia'">
                                        Tersedia
                                    </div>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Right: Summary Sidebar Card (Liquid Glass) -->
                <div class="lg:col-span-4 rounded-3xl liquid-glass p-6 sm:p-7 sticky top-6">
                    <!-- Header -->
                    <div class="text-[11px] font-bold text-teal-300 tracking-wider uppercase">
                        RINGKASAN RESERVASI
                    </div>
                    <h3 class="text-xl font-bold text-white mt-1 mb-6">
                        {{ $facility->name }}
                    </h3>

                    <!-- Details List -->
                    <div class="space-y-4 text-xs sm:text-sm">
                        <div class="flex items-center justify-between py-2 border-b border-white/10">
                            <span class="text-white/50 font-medium">Tanggal</span>
                            <span class="font-semibold text-white">
                                {{ \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('d M Y') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-white/10">
                            <span class="text-white/50 font-medium">Waktu</span>
                            <span class="font-semibold text-white text-right max-w-[60%]" x-text="timeDisplay">
                                -
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="text-white/50 font-medium">Durasi</span>
                            <span class="font-semibold text-white" x-text="durationDisplay">
                                -
                            </span>
                        </div>
                    </div>

                    <!-- Cancellation Policy Notice -->
                    <div
                        class="my-6 flex items-start gap-2.5 rounded-xl border border-teal-400/20 bg-teal-500/10 p-4 text-xs text-teal-200 font-medium leading-relaxed backdrop-blur-sm">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-teal-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Reservasi dapat dibatalkan hingga 2 jam sebelum jadwal.</span>
                    </div>

                    <!-- Action Button & Auth States -->
                    @if($isMaintenance)
                        <button disabled
                            class="w-full py-3.5 px-4 rounded-xl bg-white/10 text-white/30 text-sm font-semibold cursor-not-allowed border border-white/10">
                            Fasilitas Tidak Dapat Dipesan
                        </button>
                    @else
                        @auth
                            @php
                                $userRole = is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role;
                            @endphp
                            @if($userRole === 'user')
                                <a :href="reservationUrl"
                                    class="w-full block py-3.5 px-4 rounded-xl bg-teal-500 hover:bg-teal-400 text-white text-sm font-bold transition text-center shadow-lg shadow-teal-500/20 btn-lift cursor-pointer"
                                    :class="{ 'opacity-40 pointer-events-none': selectedSlots.length === 0 }">
                                    Lanjutkan reservasi
                                </a>
                                <p x-show="selectedSlots.length === 0" class="text-center text-[11px] text-white/40 mt-2">
                                    Pilih minimal 1 slot waktu untuk melanjutkan
                                </p>
                            @else
                                <div class="space-y-2">
                                    <div class="rounded-xl border border-amber-400/30 bg-amber-500/10 p-3 text-xs text-amber-200">
                                        Peran Anda ({{ ucfirst($userRole) }}) tidak dapat membuat reservasi pengguna.
                                    </div>
                                    <a href="{{ route('dashboard') }}"
                                        class="w-full block py-3.5 px-4 rounded-xl bg-teal-500 hover:bg-teal-400 text-white text-sm font-bold transition text-center shadow-lg shadow-teal-500/20 btn-lift">
                                        Buka Dashboard {{ ucfirst($userRole) }}
                                    </a>
                                </div>
                            @endif
                        @else
                            {{-- Case: Tamu / Belum Login --}}
                            <div class="space-y-3">
                                <div class="rounded-xl border border-amber-400/30 bg-amber-500/10 p-3.5 text-xs text-amber-200">
                                    <div class="flex items-center gap-1.5 font-semibold">
                                        <svg class="w-4 h-4 text-amber-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span>Perlu Masuk Akun</span>
                                    </div>
                                    <p class="mt-1 text-white/70 text-[11px] leading-relaxed">
                                        Anda harus masuk/login terlebih dahulu untuk dapat mengajukan reservasi ruangan ini.
                                    </p>
                                </div>

                                <a href="{{ route('login') }}"
                                    class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl bg-teal-500 hover:bg-teal-400 text-white text-sm font-bold transition text-center shadow-lg shadow-teal-500/20 btn-lift">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Masuk untuk Reservasi</span>
                                </a>

                                <p class="text-center text-xs text-white/50">
                                    Belum memiliki akun?
                                    <a href="{{ route('register') }}" class="text-teal-300 hover:text-teal-200 font-semibold hover:underline">
                                        Daftar di sini
                                    </a>
                                </p>
                            </div>
                        @endauth
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection