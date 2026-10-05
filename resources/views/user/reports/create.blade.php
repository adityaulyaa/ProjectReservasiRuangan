<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                <span class="w-2.5 h-6 rounded-full bg-rose-500"></span>
                {{ __('Laporkan Kerusakan Fasilitas') }}
            </h2>
            <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-teal-300 hover:text-teal-200 flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Riwayat Laporan
            </a>
        </div>
    </x-slot>

    <div class="py-10" x-data="{
        facilityId: '{{ old('facility_id', '') }}',
        category: '{{ old('category', '') }}',
        description: '{{ old('description', '') }}',
        photoPreview: null,
        facilities: {{ json_encode($facilities->keyBy('id')->all()) }},
        categories: {{ json_encode($categories) }},

        get selectedFacility() {
            return this.facilityId ? this.facilities[this.facilityId] : null;
        },

        handlePhotoChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.photoPreview = null;
            }
        },

        removePhoto() {
            this.photoPreview = null;
            if (this.$refs.photoInput) {
                this.$refs.photoInput.value = '';
            }
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
                    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                                class="w-full rounded-2xl border border-white/20 bg-white/5 px-4 py-3 text-sm text-white focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-400/20 transition cursor-pointer @error('facility_id') border-rose-500 @enderror"
                                required
                            >
                                <option value="" class="text-gray-900 bg-white">-- Pilih Fasilitas --</option>
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" class="text-gray-900 bg-white">
                                        {{ $facility->name }} ({{ $facility->location }})
                                    </option>
                                @endforeach
                            </select>
                            @error('facility_id')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category Selection -->
                        <div>
                            <label for="category" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Kategori Kerusakan <span class="text-rose-400">*</span>
                            </label>
                            <select 
                                id="category" 
                                name="category" 
                                x-model="category"
                                class="w-full rounded-2xl border border-white/20 bg-white/5 px-4 py-3 text-sm text-white focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-400/20 transition cursor-pointer @error('category') border-rose-500 @enderror"
                                required
                            >
                                <option value="" class="text-gray-900 bg-white">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" class="text-gray-900 bg-white">
                                        {{ ucfirst($cat) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Deskripsi Kerusakan / Masalah <span class="text-rose-400">*</span>
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="4" 
                                maxlength="2000"
                                x-model="description"
                                placeholder="Jelaskan secara detail permasalahan atau kerusakan fasilitas yang ditemukan..."
                                class="w-full rounded-2xl border border-white/20 bg-white/5 p-4 text-sm text-white placeholder-slate-500 focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-400/20 transition @error('description') border-rose-500 @enderror"
                                required
                            >{{ old('description') }}</textarea>
                            <div class="flex items-center justify-between mt-1 text-[11px] text-slate-400">
                                <span>Deskripsikan lokasi pasti dan jenis kendala yang dialami.</span>
                                <span>Maks. 2000 karakter</span>
                            </div>
                            @error('description')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Photo Upload with Preview -->
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                                Upload Foto Bukti Kerusakan <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>

                            <div class="space-y-4">
                                <!-- Custom File Input Area -->
                                <div class="relative flex items-center justify-center w-full">
                                    <label class="flex flex-col items-center justify-center w-full h-36 rounded-2xl border-2 border-dashed border-white/20 hover:border-rose-400/50 bg-white/5 hover:bg-white/10 transition cursor-pointer">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-8 h-8 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-xs text-slate-300 font-medium mb-1">
                                                <span class="text-rose-400 font-semibold">Klik untuk memilih foto</span> atau drag & drop
                                            </p>
                                            <p class="text-[11px] text-slate-400">PNG, JPG, JPEG (Maks. 2MB)</p>
                                        </div>
                                        <input 
                                            type="file" 
                                            id="photo" 
                                            name="photo" 
                                            x-ref="photoInput"
                                            @change="handlePhotoChange($event)"
                                            accept="image/jpeg,image/png,image/jpg" 
                                            class="hidden" 
                                        />
                                    </label>
                                </div>

                                <!-- Photo Preview Box -->
                                <template x-if="photoPreview">
                                    <div class="relative rounded-2xl border border-white/15 bg-black/30 p-3 flex items-center gap-4">
                                        <img :src="photoPreview" alt="Preview Foto" class="w-20 h-20 object-cover rounded-xl border border-white/20 shrink-0" />
                                        <div class="flex-1 min-w-0 text-xs">
                                            <p class="font-semibold text-white truncate">Foto Bukti Kerusakan</p>
                                            <p class="text-slate-400 text-[11px] mt-0.5">Siap diunggah bersama laporan</p>
                                        </div>
                                        <button 
                                            type="button" 
                                            @click="removePhoto()" 
                                            class="p-2 rounded-xl bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 transition text-xs font-semibold cursor-pointer shrink-0"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </template>
                            </div>

                            @error('photo')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="pt-4 flex flex-wrap items-center gap-4">
                            <button 
                                type="submit" 
                                class="inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white text-sm font-bold shadow-xl shadow-rose-500/30 hover:shadow-rose-500/50 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 ring-1 ring-white/20 cursor-pointer group"
                            >
                                <svg class="w-5 h-5 text-amber-100 group-hover:translate-x-0.5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <span>Kirim Laporan Kerusakan</span>
                            </button>

                            <a href="{{ route('facilities.index') }}" class="px-6 py-3.5 rounded-2xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-sm font-semibold transition flex items-center justify-center">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Report Summary Preview Sidebar -->
                <div class="lg:col-span-4 liquid-glass rounded-3xl border border-white/10 p-6 sm:p-7 sticky top-6 shadow-2xl space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Ringkasan Laporan
                        </h3>

                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="block text-xs text-slate-400 font-medium">Fasilitas</span>
                                <span class="font-bold text-white text-base" x-text="selectedFacility ? selectedFacility.name : 'Belum dipilih'"></span>
                                <div x-show="selectedFacility" class="text-xs text-slate-400 mt-0.5">
                                    <span x-text="selectedFacility ? selectedFacility.location : ''"></span>
                                </div>
                            </div>

                            <div class="border-t border-white/10 pt-3">
                                <span class="block text-xs text-slate-400 font-medium">Kategori</span>
                                <span class="font-semibold text-rose-300 capitalize" x-text="category || '-'"></span>
                            </div>

                            <div class="border-t border-white/10 pt-3">
                                <span class="block text-xs text-slate-400 font-medium">Foto Bukti</span>
                                <span class="font-semibold text-slate-200 text-xs" x-text="photoPreview ? 'Foto dilampirkan' : 'Tidak melampirkan foto'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Policy Info Box -->
                    <div class="rounded-2xl bg-amber-500/10 border border-amber-500/20 p-4 text-xs text-slate-300 leading-relaxed space-y-2">
                        <div class="font-semibold flex items-center gap-1.5 text-amber-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Alur Penanganan Laporan
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-slate-400">
                            <li>Laporan yang baru dikirim berstatus <strong class="text-white">Baru (New)</strong>.</li>
                            <li>Petugas akan memverifikasi dan memproses laporan Anda.</li>
                            <li>Jika dalam perbaikan, fasilitas terkait akan ditandai <strong class="text-amber-300">Maintenance</strong>.</li>
                            <li>Anda dapat memantau perkembangan status di halaman riwayat laporan.</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
