<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                    <span class="w-2.5 h-6 rounded-full bg-teal-400"></span>
                    {{ __('Kelola Pengguna & Akun') }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Pendaftaran petugas dan pengguna baru, serta verifikasi akun hasil registrasi mandiri.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-teal-500/20 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Tambah Akun Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-2xl bg-teal-500/15 border border-teal-500/30 p-4 text-sm text-teal-300 flex items-center gap-3 shadow-lg">
                    <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 text-sm text-rose-300 flex items-center gap-3 shadow-lg">
                    <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="{{ route('admin.users.index') }}" class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-teal-500/40 transition">
                    <div class="text-xs text-slate-400 font-medium">Total Akun</div>
                    <div class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</div>
                </a>

                <a href="{{ route('admin.users.index', ['status' => 'unverified']) }}" class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 hover:border-amber-500/50 transition relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-amber-300 font-medium">Menunggu Verifikasi</div>
                        @if($stats['unverified'] > 0)
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                        @endif
                    </div>
                    <div class="text-2xl font-bold text-amber-300 mt-1">{{ $stats['unverified'] }}</div>
                    <div class="text-[11px] text-amber-200/60 mt-0.5">Registrasi mandiri</div>
                </a>

                <a href="{{ route('admin.users.index', ['role' => 'staff']) }}" class="p-4 rounded-2xl bg-teal-500/10 border border-teal-500/20 hover:border-teal-500/40 transition">
                    <div class="text-xs text-teal-400 font-medium">Petugas (Staff)</div>
                    <div class="text-2xl font-bold text-teal-300 mt-1">{{ $stats['staff'] }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Pengelola fasilitas</div>
                </a>

                <a href="{{ route('admin.users.index', ['role' => 'user']) }}" class="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 hover:border-indigo-500/40 transition">
                    <div class="text-xs text-indigo-400 font-medium">Pengguna Kampus</div>
                    <div class="text-2xl font-bold text-indigo-300 mt-1">{{ $stats['user'] }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Mahasiswa/Dosen</div>
                </a>
            </div>

            {{-- Search & Filter Bar --}}
            <div class="liquid-glass rounded-3xl border border-white/10 p-5 shadow-xl">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex-1 w-full relative">
                        <input type="text"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Cari berdasarkan nama pengguna atau alamat email..."
                               class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-white/5 border border-white/15 text-white placeholder-slate-400 text-sm focus:outline-hidden focus:border-teal-400 transition" />
                        <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <select name="role" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-white/15 text-white text-sm focus:outline-hidden focus:border-teal-400 transition">
                            <option value="">Semua Peran</option>
                            <option value="user" {{ ($roleFilter ?? '') === 'user' ? 'selected' : '' }}>Pengguna (User)</option>
                            <option value="staff" {{ ($roleFilter ?? '') === 'staff' ? 'selected' : '' }}>Petugas (Staff)</option>
                            <option value="admin" {{ ($roleFilter ?? '') === 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>

                        <select name="status" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-white/15 text-white text-sm focus:outline-hidden focus:border-teal-400 transition">
                            <option value="">Semua Status</option>
                            <option value="unverified" {{ ($statusFilter ?? '') === 'unverified' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="verified" {{ ($statusFilter ?? '') === 'verified' ? 'selected' : '' }}>Terverifikasi (Aktif)</option>
                        </select>

                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-sm font-semibold text-white transition cursor-pointer">
                            Filter
                        </button>

                        @if($search || $roleFilter || ($statusFilter !== null && $statusFilter !== ''))
                            <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs text-slate-400 hover:text-white transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Users Table --}}
            <div class="liquid-glass rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                @if($users->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/10 bg-black/20 text-xs font-semibold uppercase text-slate-400 tracking-wider">
                                    <th class="py-4 px-6">Nama Pengguna</th>
                                    <th class="py-4 px-6">Alamat Email</th>
                                    <th class="py-4 px-6">Peran (Role)</th>
                                    <th class="py-4 px-6">Status Akun</th>
                                    <th class="py-4 px-6">Terdaftar Sejak</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                                @foreach($users as $user)
                                    @php
                                        $roleVal = is_object($user->role) ? $user->role->value : $user->role;
                                        $roleBadge = match($roleVal) {
                                            'admin' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                                            'staff' => 'bg-teal-500/20 text-teal-300 border-teal-500/30',
                                            'user' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                                            default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                        };
                                        $roleLabel = is_object($user->role) ? $user->role->label() : ucfirst($user->role);
                                    @endphp
                                    <tr class="hover:bg-white/[0.02] transition {{ ! $user->is_verified ? 'bg-amber-500/[0.03]' : '' }}">
                                        {{-- Name & Avatar --}}
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center font-bold text-white shrink-0 text-sm">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="font-bold text-white block">{{ $user->name }}</span>
                                                    @if(Auth::id() === $user->id)
                                                        <span class="text-[10px] text-teal-300 bg-teal-500/10 px-1.5 py-0.5 rounded border border-teal-500/20">Akun Anda</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Email --}}
                                        <td class="py-4 px-6 text-xs text-slate-300 font-mono">
                                            {{ $user->email }}
                                        </td>

                                        {{-- Role --}}
                                        <td class="py-4 px-6">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $roleBadge }}">
                                                {{ $roleLabel }}
                                            </span>
                                        </td>

                                        {{-- Verification Status --}}
                                        <td class="py-4 px-6">
                                            @if($user->is_verified)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Terverifikasi
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Menunggu Verifikasi
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Created At --}}
                                        <td class="py-4 px-6 text-xs text-slate-400">
                                            {{ $user->created_at?->format('d M Y, H:i') ?? '-' }}
                                        </td>

                                        {{-- Action Buttons --}}
                                        <td class="py-4 px-6 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                {{-- Tombol Verifikasi / Tolak jika unverified --}}
                                                @if(! $user->is_verified)
                                                    {{-- Verifikasi --}}
                                                    <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                                title="Verifikasi Akun Ini"
                                                                class="px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-xs font-semibold transition cursor-pointer flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            Verifikasi
                                                        </button>
                                                    </form>

                                                    {{-- Tolak --}}
                                                    <form action="{{ route('admin.users.reject', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Tolak atau tangguhkan verifikasi untuk akun {{ addslashes($user->name) }}?');">
                                                        @csrf
                                                        <button type="submit"
                                                                title="Tolak Verifikasi"
                                                                class="px-3 py-1.5 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 text-xs font-semibold transition cursor-pointer flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                            Tolak
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Jika sudah terverifikasi dan bukan akun admin sendiri, sediakan opsi tangguhkan --}}
                                                    @if(Auth::id() !== $user->id && $roleVal !== 'admin')
                                                        <form action="{{ route('admin.users.reject', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Tangguhkan akses untuk akun {{ addslashes($user->name) }}? Pengguna tidak akan dapat login sampai diverifikasi kembali.');">
                                                            @csrf
                                                            <button type="submit"
                                                                    title="Tangguhkan Akses"
                                                                    class="p-2 rounded-xl bg-white/5 hover:bg-amber-500/20 text-slate-400 hover:text-amber-300 border border-white/10 hover:border-amber-500/30 transition cursor-pointer">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif

                                                {{-- Edit Button --}}
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                   title="Edit Data Pengguna"
                                                   class="p-2 rounded-xl bg-teal-500/10 hover:bg-teal-500/20 text-teal-300 border border-teal-500/20 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-white/10">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="py-16 text-center">
                        <div class="w-16 h-16 rounded-3xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-white">Tidak Ada Pengguna Ditemukan</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                            @if($search || $roleFilter || ($statusFilter !== null && $statusFilter !== ''))
                                Tidak ada akun yang cocok dengan filter pencarian. Coba ubah kata kunci atau reset filter.
                            @else
                                Belum ada akun terdaftar dalam sistem.
                            @endif
                        </p>
                        @if($search || $roleFilter || ($statusFilter !== null && $statusFilter !== ''))
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-semibold text-white transition">
                                Reset Filter
                            </a>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
