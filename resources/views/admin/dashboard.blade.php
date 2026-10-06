<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white shadow-lg shadow-teal-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    Dashboard Administrator
                </h2>
                <p class="text-sm text-slate-400 mt-1 ml-[52px]">Selamat datang, {{ Auth::user()->name }}! Kelola data master fasilitas, pengguna, dan rekap sistem.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.facilities.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-teal-500/20 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Fasilitas Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Stat Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Facilities --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-teal-500/30 transition group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-2xl bg-teal-500/20 text-teal-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <a href="{{ route('admin.facilities.index') }}" class="text-xs text-teal-300 hover:text-teal-200">Lihat &rarr;</a>
                    </div>
                    <div class="text-3xl font-extrabold text-white">{{ $stats['total_facilities'] }}</div>
                    <div class="text-xs text-slate-400 mt-1 font-medium">Total Master Fasilitas</div>
                    <div class="flex items-center gap-2 mt-3 text-[11px] text-slate-400">
                        <span class="text-teal-400">{{ $stats['active_facilities'] }} aktif</span> &bull; 
                        <span class="text-amber-400">{{ $stats['maintenance_facilities'] }} perbaikan</span> &bull; 
                        <span class="text-slate-400">{{ $stats['inactive_facilities'] }} nonaktif</span>
                    </div>
                </div>

                {{-- Total Reservations --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-indigo-500/30 transition group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <a href="{{ route('admin.reports.index') }}" class="text-xs text-indigo-300 hover:text-indigo-200">Rekap &rarr;</a>
                    </div>
                    <div class="text-3xl font-extrabold text-white">{{ $stats['total_reservations'] }}</div>
                    <div class="text-xs text-slate-400 mt-1 font-medium">Total Pengajuan Reservasi</div>
                </div>

                {{-- Total Reports --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-rose-500/30 transition group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <a href="{{ route('admin.reports.index') }}" class="text-xs text-rose-300 hover:text-rose-200">Rekap &rarr;</a>
                    </div>
                    <div class="text-3xl font-extrabold text-white">{{ $stats['total_reports'] }}</div>
                    <div class="text-xs text-slate-400 mt-1 font-medium">Laporan Kerusakan Masuk</div>
                </div>

                {{-- Total Users & Verifikasi --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-amber-500/30 transition group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="text-xs text-amber-300 hover:text-amber-200">Kelola &rarr;</a>
                    </div>
                    <div class="text-3xl font-extrabold text-white">{{ $stats['total_users'] }}</div>
                    <div class="text-xs text-slate-400 mt-1 font-medium">Pengguna Terdaftar</div>
                    <div class="mt-3 text-[11px] text-amber-300">
                        {{ $stats['unverified_users'] }} akun menunggu verifikasi
                    </div>
                </div>
            </div>

            {{-- Quick Navigation / Action Row --}}
            <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-white flex items-center gap-2">
                        <span class="w-2 h-5 rounded-full bg-teal-400"></span>
                        Aksi & Navigasi Cepat
                    </h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.facilities.index') }}" class="flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-teal-500/40 transition">
                        <div class="w-12 h-12 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-white text-sm">Kelola Fasilitas</div>
                            <div class="text-xs text-slate-400 mt-0.5">Tambah, ubah, & nonaktifkan</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-amber-500/40 transition">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-white text-sm">Kelola Pengguna</div>
                            <div class="text-xs text-slate-400 mt-0.5">Verifikasi akun & tambah staf</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-emerald-500/40 transition">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-white text-sm">Rekap & Laporan</div>
                            <div class="text-xs text-slate-400 mt-0.5">Okupansi, kendala & export CSV/XLS/PDF</div>
                        </div>
                    </a>

                    <a href="{{ route('facilities.index') }}" class="flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-indigo-500/40 transition">
                        <div class="w-12 h-12 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-white text-sm">Katalog Publik</div>
                            <div class="text-xs text-slate-400 mt-0.5">Lihat sisi pengunjung</div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Recent Facilities Table --}}
            <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-white flex items-center gap-2">
                        <span class="w-2 h-5 rounded-full bg-teal-400"></span>
                        Fasilitas Terbaru Ditambahkan
                    </h3>
                    <a href="{{ route('admin.facilities.index') }}" class="text-xs font-semibold text-teal-300 hover:text-teal-200">Semua Fasilitas &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 bg-black/20 text-xs font-semibold uppercase text-slate-400 tracking-wider">
                                <th class="py-3 px-4">Nama Fasilitas</th>
                                <th class="py-3 px-4">Tipe & Lokasi</th>
                                <th class="py-3 px-4">Kapasitas</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Reservasi</th>
                                <th class="py-3 px-4 text-center">Laporan</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                            @forelse($recentFacilities as $facility)
                                @php
                                    $statusValue = is_object($facility->status) ? $facility->status->value : $facility->status;
                                    $statusLabel = is_object($facility->status) ? $facility->status->label() : ucfirst($facility->status);
                                    $badgeStyle = match($statusValue) {
                                        'active' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                        'maintenance' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                        'inactive' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                        default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                    };
                                @endphp
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="py-3 px-4 font-semibold text-white">
                                        <div class="flex items-center gap-3">
                                            @if($facility->image_url)
                                                <img src="{{ $facility->image_url }}" alt="{{ $facility->name }}" class="w-9 h-9 rounded-xl object-cover border border-white/10 shrink-0" onerror="this.style.display='none'">
                                            @else
                                                <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 shrink-0 text-xs font-bold">
                                                    {{ substr($facility->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <span>{{ $facility->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-slate-400">
                                        <div>{{ $facility->type }}</div>
                                        <div class="text-slate-500">{{ $facility->location }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-xs font-medium text-slate-300">{{ $facility->capacity }} orang</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeStyle }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center text-xs font-bold text-teal-300">{{ $facility->reservations_count }}</td>
                                    <td class="py-3 px-4 text-center text-xs font-bold text-rose-300">{{ $facility->reports_count }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('admin.facilities.show', $facility) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs text-teal-300 font-semibold transition">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-sm">Belum ada data fasilitas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
