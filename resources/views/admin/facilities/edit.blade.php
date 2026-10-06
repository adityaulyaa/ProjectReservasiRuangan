<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                <span class="w-2.5 h-6 rounded-full bg-teal-400"></span>
                {{ __('Edit Fasilitas: ') }} <span class="text-teal-300">{{ $facility->name }}</span>
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.facilities.show', $facility) }}" class="text-xs font-semibold text-slate-300 hover:text-white px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 transition">
                    Lihat Detail
                </a>
                <a href="{{ route('admin.facilities.index') }}" class="text-xs font-semibold text-teal-300 hover:text-teal-200 flex items-center gap-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{
        imageUrl: '{{ old('image_url', $facility->image_url ?? '') }}',
        imageError: false,
        name: '{{ old('name', $facility->name) }}',
        type: '{{ old('type', $facility->type) }}',
        location: '{{ old('location', $facility->location) }}',
        capacity: '{{ old('capacity', $facility->capacity) }}',
        status: '{{ old('status', is_object($facility->status) ? $facility->status->value : $facility->status) }}'
    }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-2xl bg-teal-500/15 border border-teal-500/30 p-4 text-sm text-teal-300 flex items-center gap-3 shadow-lg">
                    <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Error Summary --}}
            @if ($errors->any())
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 text-sm text-rose-300 shadow-lg">
                    <div class="font-semibold flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Terdapat kesalahan pada isian formulir:
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.facilities.update', $facility) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 shadow-2xl space-y-6">
                    <div class="border-b border-white/10 pb-4 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-white">Informasi Utama Fasilitas</h3>
                            <p class="text-xs text-slate-400 mt-1">Perbarui rincian, kapasitas, atau status operasional fasilitas.</p>
                        </div>
                        <div>
                            @php
                                $statusVal = is_object($facility->status) ? $facility->status->value : $facility->status;
                            @endphp
                            <span class="text-xs px-3 py-1 rounded-full border {{ $statusVal === 'active' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : ($statusVal === 'maintenance' ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : 'bg-slate-500/20 text-slate-300 border-slate-500/30') }}">
                                Status Saat Ini: {{ is_object($facility->status) ? $facility->status->label() : ucfirst($facility->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nama Fasilitas --}}
                        <div class="space-y-2 md:col-span-2">
                            <label for="name" class="block text-xs font-semibold text-slate-300">
                                Nama Fasilitas <span class="text-rose-400">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   x-model="name"
                                   value="{{ old('name', $facility->name) }}"
                                   maxlength="100"
                                   required
                                   class="w-full px-4 py-3 rounded-2xl bg-white/5 border border-white/15 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-teal-400 transition @error('name') border-rose-500 @enderror">
                            @error('name')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tipe Fasilitas --}}
                        <div class="space-y-2">
                            <label for="type" class="block text-xs font-semibold text-slate-300">
                                Tipe Fasilitas <span class="text-rose-400">*</span>
                            </label>
                            <input type="text"
                                   id="type"
                                   name="type"
                                   list="type-list"
                                   x-model="type"
                                   value="{{ old('type', $facility->type) }}"
                                   maxlength="50"
                                   required
                                   class="w-full px-4 py-3 rounded-2xl bg-white/5 border border-white/15 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-teal-400 transition @error('type') border-rose-500 @enderror">
                            <datalist id="type-list">
                                @foreach($commonTypes as $typeOption)
                                    <option value="{{ $typeOption }}"></option>
                                @endforeach
                            </datalist>
                            @error('type')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Lokasi Fasilitas --}}
                        <div class="space-y-2">
                            <label for="location" class="block text-xs font-semibold text-slate-300">
                                Lokasi / Gedung <span class="text-rose-400">*</span>
                            </label>
                            <input type="text"
                                   id="location"
                                   name="location"
                                   x-model="location"
                                   value="{{ old('location', $facility->location) }}"
                                   maxlength="100"
                                   required
                                   class="w-full px-4 py-3 rounded-2xl bg-white/5 border border-white/15 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-teal-400 transition @error('location') border-rose-500 @enderror">
                            @error('location')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kapasitas --}}
                        <div class="space-y-2">
                            <label for="capacity" class="block text-xs font-semibold text-slate-300">
                                Kapasitas Orang <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <input type="number"
                                       id="capacity"
                                       name="capacity"
                                       x-model="capacity"
                                       value="{{ old('capacity', $facility->capacity) }}"
                                       min="0"
                                       required
                                       class="w-full px-4 py-3 rounded-2xl bg-white/5 border border-white/15 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-teal-400 transition @error('capacity') border-rose-500 @enderror">
                                <span class="absolute right-4 top-3 text-xs text-slate-500">orang</span>
                            </div>
                            @error('capacity')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ubah Status --}}
                        <div class="space-y-2">
                            <label for="status" class="block text-xs font-semibold text-slate-300">
                                Status Fasilitas <span class="text-rose-400">*</span>
                            </label>
                            <select id="status"
                                    name="status"
                                    x-model="status"
                                    required
                                    class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-white/15 text-white text-sm focus:outline-hidden focus:border-teal-400 transition @error('status') border-rose-500 @enderror">
                                <option value="active">Aktif (Dapat Dipesan & Tampil Publik)</option>
                                <option value="maintenance">Dalam Perbaikan (Tampil Publik, Tidak Bisa Dipesan)</option>
                                <option value="inactive">Nonaktif (Disembunyikan Dari Publik)</option>
                            </select>
                            @error('status')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div class="space-y-2 md:col-span-2">
                            <label for="description" class="block text-xs font-semibold text-slate-300">
                                Deskripsi & Fasilitas Pendukung
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      maxlength="1000"
                                      placeholder="Jelaskan fasilitas pendukung seperti proyektor, AC, sound system, whiteboard, wifi, dll..."
                                      class="w-full px-4 py-3 rounded-2xl bg-white/5 border border-white/15 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-teal-400 transition @error('description') border-rose-500 @enderror">{{ old('description', $facility->description) }}</textarea>
                            @error('description')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Foto URL & Live Preview Card --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 shadow-2xl space-y-6">
                    <div class="border-b border-white/10 pb-4">
                        <h3 class="font-bold text-lg text-white">Foto & Visual Fasilitas</h3>
                        <p class="text-xs text-slate-400 mt-1">Perbarui URL gambar foto fasilitas jika ada pembaruan visual.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <div class="md:col-span-2 space-y-2">
                            <label for="image_url" class="block text-xs font-semibold text-slate-300">
                                URL Foto Fasilitas (Opsional)
                            </label>
                            <input type="url"
                                   id="image_url"
                                   name="image_url"
                                   x-model="imageUrl"
                                   @input="imageError = false"
                                   value="{{ old('image_url', $facility->image_url) }}"
                                   placeholder="https://example.com/foto-fasilitas.jpg"
                                   maxlength="500"
                                   class="w-full px-4 py-3 rounded-2xl bg-white/5 border border-white/15 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-teal-400 transition @error('image_url') border-rose-500 @enderror">
                            <p class="text-[11px] text-slate-500">
                                Masukkan tautan direct URL gambar publik.
                            </p>
                            @error('image_url')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Preview Box --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-400">Pratinjau Foto</label>
                            <div class="w-full h-36 rounded-2xl bg-white/5 border border-white/10 overflow-hidden flex items-center justify-center relative">
                                <template x-if="imageUrl && !imageError">
                                    <img :src="imageUrl"
                                         x-on:error="imageError = true"
                                         alt="Pratinjau"
                                         class="w-full h-full object-cover">
                                </template>
                                <template x-if="!imageUrl || imageError">
                                    <div class="text-center p-3 text-slate-500 text-xs">
                                        <svg class="w-8 h-8 mx-auto mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span x-text="imageError ? 'URL gambar tidak valid' : 'Belum ada foto'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between gap-4 pt-2">
                    <div>
                        {{-- Tombol Toggle Cepat --}}
                        <a href="{{ route('admin.facilities.show', $facility) }}" class="text-xs text-slate-400 hover:text-white transition">
                            Lihat riwayat reservasi fasilitas &rarr;
                        </a>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.facilities.index') }}" class="px-6 py-3 rounded-2xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm font-semibold border border-white/10 transition">
                            Batal
                        </a>
                        <button type="submit" class="px-7 py-3 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white text-sm font-semibold shadow-lg shadow-teal-500/20 transition cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
