<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                <span class="w-2.5 h-6 rounded-full bg-teal-400"></span>
                {{ __('Pendaftaran Pengguna / Petugas Baru') }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-teal-300 hover:text-teal-200 flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar Pengguna
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Info Alert --}}
            <div class="rounded-2xl bg-teal-500/10 border border-teal-500/30 p-4 text-xs text-teal-300 flex items-start gap-3 shadow-lg">
                <svg class="w-5 h-5 text-teal-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-bold block text-sm mb-0.5 text-white">Akun Langsung Aktif</span>
                    Akun yang didaftarkan secara langsung oleh Administrator akan <strong>otomatis terverifikasi</strong> dan dapat langsung digunakan untuk login ke sistem. Petugas tidak melakukan registrasi mandiri.
                </div>
            </div>

            {{-- Error Summary --}}
            @if ($errors->any())
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 text-sm text-rose-300 shadow-lg">
                    <div class="font-semibold flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Terdapat kesalahan pada formulir pendaftaran:
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 shadow-2xl space-y-6">
                    <div class="border-b border-white/10 pb-4">
                        <h3 class="font-bold text-lg text-white">Data Akun Baru</h3>
                        <p class="text-xs text-slate-400 mt-1">Lengkapi data akun untuk Pengguna atau Petugas Fasilitas kampus.</p>
                    </div>

                    {{-- Nama Lengkap --}}
                    <div class="space-y-2">
                        <label for="name" class="block text-xs font-semibold text-slate-300">
                            Nama Lengkap <span class="text-rose-400">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Contoh: Budi Santoso, S.Kom."
                               required
                               maxlength="255"
                               class="w-full px-4 py-3 rounded-2xl bg-white/5 border border-white/15 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-teal-400 transition @error('name') border-rose-500 @enderror">
                        @error('name')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alamat Email --}}
                    <div class="space-y-2">
                        <label for="email" class="block text-xs font-semibold text-slate-300">
                            Alamat Email <span class="text-rose-400">*</span>
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="Contoh: budi.santoso@kampus.ac.id"
                               required
                               maxlength="255"
                               class="w-full px-4 py-3 rounded-2xl bg-white/5 border border-white/15 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-teal-400 transition @error('email') border-rose-500 @enderror">
                        @error('email')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Peran Akun (Role) --}}
                    <div class="space-y-2">
                        <label for="role" class="block text-xs font-semibold text-slate-300">
                            Peran / Hak Akses Akun <span class="text-rose-400">*</span>
                        </label>
                        <select id="role"
                                name="role"
                                required
                                class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-white/15 text-white text-sm focus:outline-hidden focus:border-teal-400 transition @error('role') border-rose-500 @enderror">
                            <option value="">-- Pilih Peran Akun --</option>
                            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>
                                Pengguna (Mahasiswa / Dosen / Civitas Kampus)
                            </option>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>
                                Petugas Fasilitas (Staff Pemroses Reservasi & Laporan)
                            </option>
                        </select>
                        <p class="text-[11px] text-slate-500">
                            Pilih jenis akun yang didaftarkan. Peran Administrator tidak dapat didaftarkan melalui form ini.
                        </p>
                        @error('role')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kata Sandi (Password) --}}
                    <div class="space-y-2">
                        <label for="password" class="block text-xs font-semibold text-slate-300">
                            Kata Sandi Awal <span class="text-rose-400">*</span>
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Minimal 8 karakter"
                               required
                               minlength="8"
                               class="w-full px-4 py-3 rounded-2xl bg-white/5 border border-white/15 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-teal-400 transition @error('password') border-rose-500 @enderror">
                        @error('password')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-4 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="px-6 py-3 rounded-2xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm font-semibold border border-white/10 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-7 py-3 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white text-sm font-semibold shadow-lg shadow-teal-500/20 transition cursor-pointer">
                        Daftarkan Akun
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
