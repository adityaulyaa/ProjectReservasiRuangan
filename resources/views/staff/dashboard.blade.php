<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
                Dashboard Petugas
            </h2>
            <p class="text-sm text-slate-400 mt-1 ml-[52px]">Selamat datang, {{ Auth::user()->name }}! Kelola reservasi dan laporan fasilitas kampus.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Quick Stats --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Pending Reservations --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-amber-500/30 transition group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-white">{{ $pendingReservations }}</div>
                    <div class="text-xs text-slate-400 mt-1 font-medium">Reservasi Menunggu</div>
                </div>

                {{-- Approved Reservations --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-teal-500/30 transition group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-2xl bg-teal-500/20 text-teal-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-white">{{ $approvedReservations }}</div>
                    <div class="text-xs text-slate-400 mt-1 font-medium">Reservasi Disetujui</div>
                </div>

                {{-- New Reports --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-rose-500/30 transition group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-white">{{ $newReports }}</div>
                    <div class="text-xs text-slate-400 mt-1 font-medium">Laporan Baru</div>
                </div>

                {{-- In Progress Reports --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-indigo-500/30 transition group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-white">{{ $inProgressReports }}</div>
                    <div class="text-xs text-slate-400 mt-1 font-medium">Laporan Diproses</div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('staff.reservations.queue') }}" 
                   class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-teal-500/30 transition group flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-teal-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-white group-hover:text-teal-300 transition">Antrian Reservasi</h3>
                        <p class="text-xs text-slate-400 mt-1">Proses pengajuan reservasi fasilitas kampus</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-teal-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('staff.reports.queue') }}" 
                   class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl hover:border-rose-500/30 transition group flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-500 flex items-center justify-center text-white shadow-lg shadow-rose-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-white group-hover:text-rose-300 transition">Antrian Laporan Kerusakan</h3>
                        <p class="text-xs text-slate-400 mt-1">Proses laporan kerusakan fasilitas kampus</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-rose-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
