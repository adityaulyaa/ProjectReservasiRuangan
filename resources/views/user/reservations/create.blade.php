<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                <span class="w-2.5 h-6 rounded-full bg-teal-400"></span>
                {{ __('Ajukan Reservasi Fasilitas') }}
            </h2>
            <a href="{{ route('facilities.index') }}" class="text-xs font-semibold text-teal-300 hover:text-teal-200 flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar Fasilitas
            </a>
        </div>
    </x-slot>

    <div class="py-10" x-data="{
        facilityId: '{{ old('facility_id', $facilityId ?? '') }}',
        date: '{{ old('reservation_date', $date ?? now(config('app.timezone'))->toDateString()) }}',
        startTime: '{{ old('start_time', $startTime ?? '07:00') }}',
        endTime: '{{ old('end_time', $endTime ?? '07:30') }}',
        facilities: {{ json_encode($facilities->keyBy('id')->all()) }},
        allSlots: {{ json_encode($availableSlots) }},
        today: '{{ now(config('app.timezone'))->toDateString() }}',
        currentTime: '{{ now(config('app.timezone'))->format('H:i') }}',
        closeTime: '{{ config('reservation.close_time', '20:00') }}',
        slotMinutes: {{ (int) config('reservation.slot_minutes', 30) }},

        init() {
            this.onDateChange();

            if (this.durationMinutes <= 0) {
                this.setDurationSlots(1);
            }
        },

        isPastSlot(slot) {
            return this.date === this.today && slot <= this.currentTime;
        },

        onDateChange() {
            if (this.isPastSlot(this.startTime)) {
                const nextSlot = this.allSlots.find((slot) => !this.isPastSlot(slot));
                this.startTime = nextSlot || '';
            }

            if (this.startTime) {
                this.setDurationSlots(1);
            }
        },

        onStartTimeChange() {
            this.setDurationSlots(1);
        },

        setDurationSlots(slots) {
            if (!this.startTime) return;
            const [sh, sm] = this.startTime.split(':').map(Number);
            const startMins = sh * 60 + sm;
            const newEndMins = startMins + (slots * this.slotMinutes);
            const [ch, cm] = this.closeTime.split(':').map(Number);
            const closeMins = ch * 60 + cm;

            const cappedMins = Math.min(newEndMins, closeMins);
            const eh = String(Math.floor(cappedMins / 60)).padStart(2, '0');
            const em = String(cappedMins % 60).padStart(2, '0');
            this.endTime = `${eh}:${em}`;
        },

        get availableEndTimes() {
            if (!this.startTime) return [];
            const [sh, sm] = this.startTime.split(':').map(Number);
            const startMins = sh * 60 + sm;
            const [ch, cm] = this.closeTime.split(':').map(Number);
            const closeMins = ch * 60 + cm;
            
            let results = [];
            let curr = startMins + this.slotMinutes;
            while (curr <= closeMins) {
                const h = String(Math.floor(curr / 60)).padStart(2, '0');
                const m = String(curr % 60).padStart(2, '0');
                const timeStr = `${h}:${m}`;
                const diff = curr - startMins;
                const slotCount = Math.round(diff / this.slotMinutes);
                const hours = Math.floor(diff / 60);
                const remainingMins = diff % 60;
                
                let durStr = '';
                if (hours > 0 && remainingMins > 0) {
                    durStr = `${hours} jam ${remainingMins} mnt`;
                } else if (hours > 0) {
                    durStr = `${hours} jam`;
                } else {
                    durStr = `${remainingMins} mnt`;
                }

                const suffix = slotCount === 1 ? ' · 1 Slot (Standar Sesuai Jadwal)' : ` · ${slotCount} Slot (${durStr})`;
                
                results.push({
                    time: timeStr,
                    slots: slotCount,
                    label: `${timeStr}${suffix}`
                });
                curr += this.slotMinutes;
            }
            return results;
        },

        get selectedFacility() {
            return this.facilityId ? this.facilities[this.facilityId] : null;
        },

        get durationMinutes() {
            if (!this.startTime || !this.endTime) return 0;
            const [sh, sm] = this.startTime.split(':').map(Number);
            const [eh, em] = this.endTime.split(':').map(Number);
            const startTotal = sh * 60 + sm;
            const endTotal = eh * 60 + em;
            return endTotal > startTotal ? (endTotal - startTotal) : 0;
        },

        get slotCount() {
            return Math.round(this.durationMinutes / this.slotMinutes);
        },

        get durationDisplay() {
            const mins = this.durationMinutes;
            if (mins <= 0) return '-';
            const hours = Math.floor(mins / 60);
            const remainingMins = mins % 60;
            let dur = '';
            if (hours > 0 && remainingMins > 0) {
                dur = `${hours} Jam ${remainingMins} Menit`;
            } else if (hours > 0) {
                dur = `${hours} Jam`;
            } else {
                dur = `${mins} Menit`;
            }
            return `${this.slotCount} Slot · ${dur}`;
        },

        get isValidTime() {
            return this.durationMinutes > 0;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Success / Error Alert -->
            @if(session('success'))
                <div class="rounded-2xl bg-teal-500/15 border border-teal-500/30 p-4 text-sm text-teal-300 flex items-center gap-3 shadow-lg">
                    <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 text-sm text-rose-300 shadow-lg">
                    <div class="flex items-center gap-2 font-semibold mb-1 text-rose-200">
                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Terdapat kesalahan pada formulir:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 ml-6 text-rose-300/90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Left Column: Form -->
                <div class="lg:col-span-8 liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 shadow-2xl">
                    <form action="{{ route('reservations.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Facility Selection -->
                        <div>
                            <label for="facility_id" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Fasilitas / Ruangan <span class="text-rose-400">*</span>
                            </label>
                            <select 
                                id="facility_id" 
                                name="facility_id" 
                                x-model="facilityId"
                                class="w-full rounded-2xl border border-white/20 bg-white/5 px-4 py-3 text-sm text-white focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition cursor-pointer @error('facility_id') border-rose-500 @enderror"
                                required
                            >
                                <option value="" class="text-gray-900 bg-white">-- Pilih Fasilitas --</option>
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" class="text-gray-900 bg-white">
                                        {{ $facility->name }} ({{ $facility->location }} - Kapasitas: {{ $facility->capacity }} orang)
                                    </option>
                                @endforeach
                            </select>
                            @error('facility_id')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date Selection -->
                        <div>
                            <label for="reservation_date" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Tanggal Penggunaan <span class="text-rose-400">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="reservation_date" 
                                name="reservation_date" 
                                x-model="date"
                                @change="onDateChange()"
                                min="{{ now(config('app.timezone'))->toDateString() }}"
                                class="w-full rounded-2xl border border-white/20 bg-white/5 px-4 py-3 text-sm text-white focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition cursor-pointer @error('reservation_date') border-rose-500 @enderror"
                                required
                            >
                            @error('reservation_date')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Time Slots Grid -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Start Time -->
                                <div>
                                    <label for="start_time" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                        Waktu Mulai <span class="text-rose-400">*</span>
                                    </label>
                                    <select 
                                        id="start_time" 
                                        name="start_time" 
                                        x-model="startTime"
                                        @change="onStartTimeChange()"
                                        class="w-full rounded-2xl border border-white/20 bg-white/5 px-4 py-3 text-sm text-white focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition cursor-pointer @error('start_time') border-rose-500 @enderror"
                                        required
                                    >
                                        @foreach($availableSlots as $slot)
                                            <option value="{{ $slot }}" x-bind:disabled="isPastSlot('{{ $slot }}')" class="text-gray-900 bg-white">{{ $slot }}</option>
                                        @endforeach
                                    </select>
                                    @error('start_time')
                                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                    <p x-show="date === today && allSlots.every((slot) => isPastSlot(slot))" class="mt-1 text-xs text-amber-300">
                                        Tidak ada slot tersisa untuk hari ini. Silakan pilih tanggal lain.
                                    </p>
                                </div>

                                <!-- End Time -->
                                <div>
                                    <label for="end_time" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                        Waktu Selesai <span class="text-rose-400">*</span>
                                    </label>
                                    <select 
                                        id="end_time" 
                                        name="end_time" 
                                        x-model="endTime"
                                        class="w-full rounded-2xl border border-white/20 bg-white/5 px-4 py-3 text-sm text-white focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition cursor-pointer @error('end_time') border-rose-500 @enderror"
                                        required
                                    >
                                        <template x-for="item in availableEndTimes" :key="item.time">
                                            <option :value="item.time" x-text="item.label" class="text-gray-900 bg-white"></option>
                                        </template>
                                    </select>
                                    @error('end_time')
                                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Quick Duration Selector -->
                            <div class="pt-1">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-2">
                                    Pilih Durasi Cepat (Kelipatan 30 Menit Sesuai Slot):
                                </span>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="setDurationSlots(1)" 
                                            class="px-3 py-1.5 rounded-xl border text-xs font-medium transition cursor-pointer"
                                            :class="slotCount === 1 ? 'border-teal-400 bg-teal-500/20 text-teal-300 font-bold' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'">
                                        1 Slot (30 Menit)
                                    </button>
                                    <button type="button" @click="setDurationSlots(2)" 
                                            class="px-3 py-1.5 rounded-xl border text-xs font-medium transition cursor-pointer"
                                            :class="slotCount === 2 ? 'border-teal-400 bg-teal-500/20 text-teal-300 font-bold' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'">
                                        2 Slot (1 Jam)
                                    </button>
                                    <button type="button" @click="setDurationSlots(3)" 
                                            class="px-3 py-1.5 rounded-xl border text-xs font-medium transition cursor-pointer"
                                            :class="slotCount === 3 ? 'border-teal-400 bg-teal-500/20 text-teal-300 font-bold' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'">
                                        3 Slot (1.5 Jam)
                                    </button>
                                    <button type="button" @click="setDurationSlots(4)" 
                                            class="px-3 py-1.5 rounded-xl border text-xs font-medium transition cursor-pointer"
                                            :class="slotCount === 4 ? 'border-teal-400 bg-teal-500/20 text-teal-300 font-bold' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'">
                                        4 Slot (2 Jam)
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Time Validation Warning -->
                        <div x-show="!isValidTime && startTime && endTime" class="p-3.5 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-xs text-amber-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Waktu selesai harus lebih besar daripada waktu mulai (minimal 1 slot 30 menit).</span>
                        </div>

                        <!-- Purpose / Tujuan Penggunaan -->
                        <div>
                            <label for="purpose" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Tujuan Penggunaan <span class="text-rose-400">*</span>
                            </label>
                            <textarea 
                                id="purpose" 
                                name="purpose" 
                                rows="3" 
                                maxlength="255"
                                placeholder="Contoh: Rapat koordinasi organisasi mahasiswa membahas persiapan kegiatan..."
                                class="w-full rounded-2xl border border-white/20 bg-white/5 p-4 text-sm text-white placeholder-slate-500 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400/20 transition @error('purpose') border-rose-500 @enderror"
                                required
                            >{{ old('purpose') }}</textarea>
                            <div class="flex items-center justify-between mt-1 text-[11px] text-slate-400">
                                <span>Jelaskan kegiatan yang akan dilakukan di ruangan ini.</span>
                                <span>Maks. 255 karakter</span>
                            </div>
                            @error('purpose')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="pt-4 flex items-center gap-4">
                            <button 
                                type="submit" 
                                :disabled="!startTime || !isValidTime"
                                class="px-7 py-3 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold shadow-lg shadow-teal-500/20 transition cursor-pointer"
                            >
                                Kirim Pengajuan Reservasi
                            </button>

                            <a href="{{ route('reservations.index') }}" class="px-6 py-3 rounded-2xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-sm font-semibold transition">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Reservation Preview Sidebar -->
                <div class="lg:col-span-4 liquid-glass rounded-3xl border border-white/10 p-6 sm:p-7 sticky top-6 shadow-2xl">
                    <h3 class="text-base font-bold text-white border-b border-white/10 pb-3 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Ringkasan Pengajuan
                    </h3>

                    <div class="space-y-4 text-sm">
                        <!-- Facility info preview -->
                        <div>
                            <span class="block text-xs text-slate-400 font-medium">Ruangan Terpilih</span>
                            <span class="font-bold text-white text-base" x-text="selectedFacility ? selectedFacility.name : 'Belum dipilih'"></span>
                            <div x-show="selectedFacility" class="text-xs text-slate-400 mt-0.5">
                                <span x-text="selectedFacility ? selectedFacility.location : ''"></span> · 
                                <span x-text="selectedFacility ? `Kapasitas ${selectedFacility.capacity} orang` : ''"></span>
                            </div>
                        </div>

                        <!-- Date preview -->
                        <div class="border-t border-white/10 pt-3">
                            <span class="block text-xs text-slate-400 font-medium">Tanggal</span>
                            <span class="font-semibold text-white" x-text="date || '-'"></span>
                        </div>

                        <!-- Time preview -->
                        <div class="border-t border-white/10 pt-3">
                            <span class="block text-xs text-slate-400 font-medium">Waktu Penggunaan</span>
                            <span class="font-semibold text-teal-300" x-text="startTime && endTime ? `${startTime} – ${endTime} WIB` : '-'"></span>
                        </div>

                        <!-- Duration preview -->
                        <div class="border-t border-white/10 pt-3">
                            <span class="block text-xs text-slate-400 font-medium">Total Slot & Durasi</span>
                            <span class="font-bold text-teal-300 text-sm" x-text="durationDisplay"></span>
                        </div>
                    </div>

                    <!-- Policy Box -->
                    <div class="mt-6 rounded-2xl bg-teal-500/10 border border-teal-500/20 p-4 text-xs text-slate-300 leading-relaxed">
                        <div class="font-semibold flex items-center gap-1.5 mb-1.5 text-teal-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Ketentuan Slot Reservasi
                        </div>
                        <ul class="list-disc list-inside space-y-1.5 text-slate-400">
                            <li>Setiap slot berdurasi <strong class="text-white">30 menit</strong> (misal 07:00 – 07:30 = 1 slot).</li>
                            <li>Anda dapat memesan <strong class="text-white">1 slot</strong> atau lebih secara berurutan.</li>
                            <li>Reservasi dapat dibatalkan hingga <strong class="text-white">2 jam</strong> sebelum waktu mulai.</li>
                            <li>Jam operasional ruangan adalah <strong class="text-white">07:00 – 20:00</strong>.</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
