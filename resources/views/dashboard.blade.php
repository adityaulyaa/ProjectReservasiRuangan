<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                    <span class="w-2.5 h-6 rounded-full bg-teal-400"></span>
                    {{ __('Dashboard Pengguna') }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Selamat datang kembali, <span class="font-semibold text-amber-200">{{ Auth::user()->name }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reservations.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-teal-500/20 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Reservasi Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                {{-- Total Reservasi --}}
                <div class="liquid-glass rounded-3xl p-6 border border-white/10 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-1">Total Pengajuan</span>
                        <span class="text-3xl font-extrabold text-white">{{ $stats['total_reservations'] ?? 0 }}</span>
                        <span class="text-[11px] text-slate-400 block mt-1">Keseluruhan reservasi</span>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-teal-500/15 border border-teal-500/30 text-teal-400 flex items-center justify-center shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                {{-- Menunggu Persetujuan --}}
                <div class="liquid-glass rounded-3xl p-6 border border-white/10 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-300/80 block mb-1">Menunggu Persetujuan</span>
                        <span class="text-3xl font-extrabold text-amber-300">{{ $stats['pending_reservations'] ?? 0 }}</span>
                        <span class="text-[11px] text-amber-200/60 block mt-1">Dalam antrian verifikasi</span>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-300 flex items-center justify-center shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Disetujui --}}
                <div class="liquid-glass rounded-3xl p-6 border border-white/10 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-300/80 block mb-1">Disetujui</span>
                        <span class="text-3xl font-extrabold text-emerald-400">{{ $stats['approved_reservations'] ?? 0 }}</span>
                        <span class="text-[11px] text-emerald-200/60 block mt-1">Jadwal telah terkunci</span>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Quick Links / Navigation Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('reservations.index') }}" class="group liquid-glass rounded-3xl p-6 border border-white/10 hover:border-teal-400/40 hover:bg-white/10 transition flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 group-hover:bg-teal-500/20 text-slate-300 group-hover:text-teal-300 flex items-center justify-center border border-white/10 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-white group-hover:text-teal-300 transition text-base">Riwayat Reservasi Saya</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Pantau status, lihat catatan petugas, dan kelola pembatalan</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-teal-300 transition transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('facilities.index') }}" class="group liquid-glass rounded-3xl p-6 border border-white/10 hover:border-teal-400/40 hover:bg-white/10 transition flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 group-hover:bg-teal-500/20 text-slate-300 group-hover:text-teal-300 flex items-center justify-center border border-white/10 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-white group-hover:text-teal-300 transition text-base">Katalog Fasilitas & Ruangan</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Eksplorasi kapasitas ruangan dan cek slot jadwal ketersediaan</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-teal-300 transition transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- Recent Reservations List --}}
            <div class="liquid-glass rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10 flex items-center justify-between">
                    <h3 class="font-bold text-base text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Aktivitas Reservasi Terkini
                    </h3>
                    <a href="{{ route('reservations.index') }}" class="text-xs font-semibold text-teal-300 hover:text-teal-200 transition flex items-center gap-1">
                        <span>Lihat Semua Riwayat</span>
                        <span>→</span>
                    </a>
                </div>

                @if($recentReservations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-black/20 text-xs font-semibold uppercase text-slate-400 tracking-wider border-b border-white/10">
                                    <th class="py-4 px-6">Fasilitas / Ruangan</th>
                                    <th class="py-4 px-6">Tanggal</th>
                                    <th class="py-4 px-6">Waktu</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                                @foreach($recentReservations as $res)
                                    @php
                                        $statusValue = is_object($res->status) ? $res->status->value : $res->status;
                                        $badgeClasses = [
                                            'pending' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                            'approved' => 'bg-teal-500/20 text-teal-300 border-teal-500/30',
                                            'rejected' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                            'cancelled' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                        ][$statusValue] ?? 'bg-white/10 text-slate-300 border-white/20';

                                        $badgeLabels = [
                                            'pending' => 'Menunggu',
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            'cancelled' => 'Dibatalkan',
                                        ][$statusValue] ?? ucfirst($statusValue);
                                    @endphp
                                    <tr class="hover:bg-white/5 transition">
                                        <td class="py-4 px-6 font-semibold text-white">
                                            {{ $res->facility->name ?? '-' }}
                                            <span class="block text-xs font-normal text-slate-400 mt-0.5">
                                                {{ $res->facility->location ?? '' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap text-slate-300">
                                            {{ \Illuminate\Support\Carbon::parse($res->reservation_date)->locale('id')->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap font-medium text-teal-300">
                                            {{ substr($res->start_time, 0, 5) }} – {{ substr($res->end_time, 0, 5) }} WIB
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClasses }}">
                                                {{ $badgeLabels }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap text-right">
                                            <a href="{{ route('reservations.show', $res->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-200 hover:text-white text-xs font-medium transition cursor-pointer">
                                                <span>Detail</span>
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-16 text-center text-slate-400">
                        <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-white">Belum ada aktivitas reservasi</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Mulai pesan fasilitas untuk kegiatan atau rapat Anda sekarang.</p>
                        <a href="{{ route('reservations.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 mt-5 bg-teal-500 hover:bg-teal-400 text-white text-xs font-semibold rounded-xl shadow-lg shadow-teal-500/20 transition cursor-pointer">
                            Ajukan Reservasi Baru
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
