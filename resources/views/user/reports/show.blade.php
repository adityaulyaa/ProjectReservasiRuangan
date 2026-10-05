<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.index') }}" class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition">
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

            <div class="flex items-center gap-3">
                <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-rose-600 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white text-xs font-semibold rounded-xl shadow-lg transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Laporan Baru
                </a>
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

            {{-- Info Resolusi Petugas (Jika ada) --}}
            @if($report->resolution_note)
                <div class="liquid-glass rounded-3xl border border-teal-500/30 bg-teal-950/30 p-6 flex items-start gap-4 shadow-xl">
                    <div class="w-10 h-10 rounded-2xl bg-teal-500/20 text-teal-300 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-teal-200">Catatan Resolusi dari Petugas</h4>
                        <p class="text-sm text-slate-200 leading-relaxed italic bg-white/5 p-3 rounded-xl border border-white/10 mt-2">
                            "{{ $report->resolution_note }}"
                        </p>
                        @if($report->processedBy)
                            <p class="text-xs text-teal-300/80 mt-1">
                                Ditangani oleh: <span class="font-semibold text-white">{{ $report->processedBy->name }}</span>
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column: Detail Laporan & Fasilitas (2 spans) --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Card Info Fasilitas --}}
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-4 shadow-2xl">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Fasilitas yang Dilaporkan
                            </h3>
                            <a href="{{ route('facilities.availability', ['id' => $report->facility_id]) }}" 
                               target="_blank"
                               class="text-xs font-semibold text-teal-300 hover:text-teal-200 hover:underline flex items-center gap-1">
                                <span>Cek Fasilitas</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>

                        <div>
                            <h4 class="text-xl font-bold text-white">
                                {{ $report->facility->name ?? 'Fasilitas' }}
                            </h4>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-300 mt-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/10">
                                    <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    {{ $report->facility->type ?? '-' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/10">
                                    <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    {{ $report->facility->location ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Detail Rincian & Deskripsi --}}
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-5 shadow-2xl">
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Deskripsi Rincian Kerusakan
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                                <span class="text-xs text-slate-400 font-medium block mb-1">Kategori Kerusakan</span>
                                <span class="font-bold text-amber-300 text-base capitalize">
                                    {{ $report->category }}
                                </span>
                            </div>
                            <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                                <span class="text-xs text-slate-400 font-medium block mb-1">Tanggal & Waktu Laporan</span>
                                <span class="font-bold text-white text-base">
                                    {{ $report->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                            </div>
                        </div>

                        <div>
                            <span class="text-xs text-slate-400 font-medium block mb-1.5">Penjelasan / Deskripsi Lengkap</span>
                            <div class="bg-white/5 rounded-2xl p-4 border border-white/10 text-sm text-slate-200 leading-relaxed whitespace-pre-line">
                                {{ $report->description }}
                            </div>
                        </div>
                    </div>

                    {{-- Card Foto Bukti Kerusakan --}}
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-4 shadow-2xl">
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Foto Bukti Kerusakan
                        </h3>

                        @if($photoUrl)
                            <div class="space-y-3">
                                <div class="relative rounded-2xl border border-white/15 bg-black/40 overflow-hidden group max-w-xl">
                                    <img src="{{ $photoUrl }}" alt="Foto Kerusakan {{ $report->facility->name }}" class="w-full h-auto max-h-96 object-contain transition group-hover:scale-105 duration-300" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ $photoUrl }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-300 hover:text-teal-200 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Buka Gambar di Tab Baru
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-xs font-medium text-slate-300">Tidak ada foto dilampirkan</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Pengajuan laporan ini dibuat tanpa melampirkan foto bukti.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right Column: Log Aktivitas & Status Timeline (1 span) --}}
                <div class="space-y-6">
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-4 shadow-2xl">
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Timeline & Log Status
                        </h3>

                        @if($report->logs->count() > 0)
                            <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-white/20">
                                @foreach($report->logs as $log)
                                    <div class="relative group">
                                        {{-- Bullet circle --}}
                                        <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full bg-rose-400 ring-4 ring-slate-900 border-2 border-rose-500"></div>

                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-white uppercase tracking-wider">
                                                    {{ $log->action }}
                                                </span>
                                                <span class="text-[11px] text-slate-400">
                                                    {{ $log->created_at->locale('id')->diffForHumans() }}
                                                </span>
                                            </div>

                                            <p class="text-xs text-slate-300">
                                                Oleh: <span class="font-medium text-amber-200">{{ $log->actor->name ?? 'Sistem' }}</span>
                                            </p>

                                            @if($log->new_status)
                                                <div class="text-[11px] text-slate-400">
                                                    Status: <span class="font-semibold text-rose-300">{{ ucfirst($log->new_status) }}</span>
                                                </div>
                                            @endif

                                            @if($log->note)
                                                <p class="text-xs text-slate-300 bg-white/5 p-2.5 rounded-xl border border-white/10 mt-1 italic">
                                                    "{{ $log->note }}"
                                                </p>
                                            @endif

                                            <div class="text-[10px] text-slate-400">
                                                {{ $log->created_at->format('d M Y, H:i') }} WIB
                                            </div>
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
