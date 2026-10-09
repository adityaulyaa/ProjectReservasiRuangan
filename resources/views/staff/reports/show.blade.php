<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.reports.queue') }}" class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="font-bold text-2xl text-white tracking-tight">
                            Laporan Kerusakan #{{ $report->id }}
                        </h2>
                        @php
                            $statusValue = is_object($report->status) ? $report->status->value : $report->status;
                            $badgeClasses = [
                                'new' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                'in_progress' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                'resolved' => 'bg-teal-500/20 text-teal-300 border-teal-500/30',
                                'rejected' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                            ][$statusValue] ?? 'bg-white/10 text-slate-300 border-white/20';

                            $badgeLabels = [
                                'new' => 'Baru',
                                'in_progress' => 'Sedang Diproses',
                                'resolved' => 'Selesai',
                                'rejected' => 'Ditolak',
                            ][$statusValue] ?? ucfirst($statusValue);
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClasses }}">
                            {{ $badgeLabels }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        Dilaporkan pada {{ $report->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB oleh {{ $report->user->name ?? 'Pengguna' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('staff.reports.markMaintenance', $report->id) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 text-amber-300 border border-amber-500/30 hover:bg-amber-500/10 text-xs font-semibold rounded-xl transition cursor-pointer">
                        Tandai Perbaikan
                    </button>
                </form>
                <form method="POST" action="{{ route('staff.reports.markActive', $report->id) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 text-teal-300 border border-teal-500/30 hover:bg-teal-500/10 text-xs font-semibold rounded-xl transition cursor-pointer">
                        Aktifkan Fasilitas
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-2xl bg-teal-500/15 border border-teal-500/30 p-4 text-sm text-teal-300 flex items-center gap-3 shadow-lg">
                    <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 text-sm text-rose-300 space-y-1 shadow-lg">
                    <div class="flex items-center gap-2 font-semibold text-rose-200">
                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Terjadi kesalahan:
                    </div>
                    <ul class="list-disc list-inside pl-7 text-xs text-rose-300/90">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    {{-- Fasilitas --}}
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-4 shadow-2xl">
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3">Fasilitas yang Dilaporkan</h3>
                        <div>
                            <h4 class="text-xl font-bold text-white">{{ $report->facility->name ?? 'Fasilitas' }}</h4>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-300 mt-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/10">
                                    {{ $report->facility->type ?? '-' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/10">
                                    {{ $report->facility->location ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Detail --}}
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-5 shadow-2xl">
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3">Deskripsi Rincian Kerusakan</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                                <span class="text-xs text-slate-400 font-medium block mb-1">Kategori Kerusakan</span>
                                <span class="font-bold text-amber-300 text-base capitalize">{{ $report->category }}</span>
                            </div>
                            <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                                <span class="text-xs text-slate-400 font-medium block mb-1">Tanggal Laporan</span>
                                <span class="font-bold text-white text-base">{{ $report->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-medium block mb-1.5">Penjelasan / Deskripsi Lengkap</span>
                            <div class="bg-white/5 rounded-2xl p-4 border border-white/10 text-sm text-slate-200 leading-relaxed whitespace-pre-line">
                                {{ $report->description }}
                            </div>
                        </div>
                    </div>

                    {{-- Foto --}}
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-4 shadow-2xl">
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3">Foto Bukti Kerusakan</h3>
                        @if($photoUrl)
                            <div class="space-y-3">
                                <div class="relative rounded-2xl border border-white/15 bg-black/40 overflow-hidden max-w-xl">
                                    <img src="{{ $photoUrl }}" alt="Foto Kerusakan {{ $report->facility->name }}" class="w-full h-auto max-h-96 object-contain" />
                                </div>
                                <a href="{{ $photoUrl }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-300 hover:text-teal-200 transition">
                                    Buka Gambar di Tab Baru
                                </a>
                            </div>
                        @else
                            <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-center text-slate-400">
                                <p class="text-xs font-medium text-slate-300">Tidak ada foto dilampirkan</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Log --}}
                <div class="space-y-6">
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-4 shadow-2xl">
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3">Timeline & Log Status</h3>
                        @if($report->logs->count() > 0)
                            <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-white/20">
                                @foreach($report->logs as $log)
                                    <div class="relative">
                                        <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full bg-rose-400 ring-4 ring-slate-900 border-2 border-rose-500"></div>
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-white uppercase tracking-wider">{{ $log->action }}</span>
                                                <span class="text-[11px] text-slate-400">{{ $log->created_at->locale('id')->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-xs text-slate-300">
                                                Oleh: <span class="font-medium text-amber-200">{{ $log->actor->name ?? 'Sistem' }}</span>
                                            </p>
                                            @if($log->new_status)
                                                <div class="text-[11px] text-slate-400">
                                                    Status: <span class="font-semibold text-rose-300">{{ ucfirst(str_replace('_', ' ', $log->new_status)) }}</span>
                                                </div>
                                            @endif
                                            @if($log->note)
                                                <p class="text-xs text-slate-300 bg-white/5 p-2.5 rounded-xl border border-white/10 mt-1 italic">
                                                    "{{ $log->note }}"
                                                </p>
                                            @endif
                                            <div class="text-[10px] text-slate-400">{{ $log->created_at->format('d M Y, H:i') }} WIB</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">Belum ada riwayat aktivitas tercatat.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
