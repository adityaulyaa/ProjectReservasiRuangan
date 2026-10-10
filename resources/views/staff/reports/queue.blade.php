<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-500 flex items-center justify-center text-white shadow-lg shadow-rose-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    Antrian Laporan Kerusakan
                </h2>
                <p class="text-sm text-slate-400 mt-1 ml-[52px]">Proses laporan kerusakan fasilitas kampus</p>
            </div>

            {{-- Filter Status --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('staff.reports.queue') }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition {{ !$statusFilter ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10 hover:text-white' }}">
                    Semua
                </a>
                <a href="{{ route('staff.reports.queue', ['status' => 'new']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition {{ $statusFilter === 'new' ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10 hover:text-white' }}">
                    Baru
                </a>
                <a href="{{ route('staff.reports.queue', ['status' => 'in_progress']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition {{ $statusFilter === 'in_progress' ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10 hover:text-white' }}">
                    Diproses
                </a>
                <a href="{{ route('staff.reports.queue', ['status' => 'under_repair']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition {{ $statusFilter === 'under_repair' ? 'bg-orange-500/20 text-orange-300 border-orange-500/30' : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10 hover:text-white' }}">
                    Diperbaiki
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{ resolveId: null, resolveName: '', rejectId: null, rejectName: '' }">
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

            @if($reports->count() > 0)
                <div class="space-y-4">
                    @foreach($reports as $report)
                        @php
                            $statusValue = is_object($report->status) ? $report->status->value : $report->status;
                            $isNew = $statusValue === 'new';
                            $isInProgress = $statusValue === 'in_progress';
                            $isUnderRepair = $statusValue === 'under_repair';
                            $badgeClasses = [
                                'new' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                'in_progress' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                'under_repair' => 'bg-orange-500/20 text-orange-300 border-orange-500/30',
                            ][$statusValue] ?? 'bg-white/10 text-slate-300 border-white/20';
                            $badgeLabels = [
                                'new' => 'Baru',
                                'in_progress' => 'Sedang Diproses',
                                'under_repair' => 'Sedang Diperbaiki',
                            ][$statusValue] ?? ucfirst($statusValue);
                        @endphp
                        <div class="liquid-glass rounded-3xl border border-white/10 p-5 sm:p-6 shadow-2xl transition hover:border-white/20">
                            <div class="flex flex-col lg:flex-row lg:items-start gap-5">
                                {{-- Left: Info --}}
                                <div class="flex-1 space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClasses }}">
                                            {{ $badgeLabels }}
                                        </span>
                                        <span class="text-xs text-slate-400">#{{ $report->id }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/5 border border-white/10 text-[11px] text-amber-300 capitalize">
                                            {{ $report->category }}
                                        </span>
                                    </div>

                                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                        <div class="flex-1 space-y-1">
                                            <h4 class="text-base font-bold text-white">{{ $report->facility->name ?? 'Fasilitas' }}</h4>
                                            <p class="text-xs text-slate-400">{{ $report->facility->location ?? '-' }}</p>
                                        </div>
                                        <div class="sm:text-right">
                                            <span class="text-[11px] font-medium text-slate-400 block mb-1">Pelapor</span>
                                            <div class="flex sm:justify-end items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shadow">
                                                    {{ strtoupper(substr($report->user->name ?? '?', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-white">{{ $report->user->name ?? '-' }}</div>
                                                    <div class="text-[11px] text-slate-400">{{ $report->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                                        <span class="text-[11px] font-medium text-slate-400 block mb-1">Deskripsi Kerusakan</span>
                                        <p class="text-xs text-slate-200 leading-relaxed line-clamp-2">{{ $report->description }}</p>
                                    </div>
                                </div>

                                {{-- Right: Actions --}}
                                <div class="flex flex-row lg:flex-col items-stretch gap-2 lg:w-44 shrink-0">
                                    <a href="{{ route('staff.reports.show', $report->id) }}"
                                       class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10 hover:text-white transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat Detail
                                    </a>

                                    @if($isNew)
                                        {{-- Mulai Proses --}}
                                        <form method="POST" action="{{ route('staff.reports.updateStatus', $report->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit"
                                                    class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white shadow-lg shadow-amber-600/20 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                Mulai Proses
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Tandai Perbaikan --}}
                                    @if($isNew || $isInProgress)
                                        <form method="POST" action="{{ route('staff.reports.markMaintenance', $report->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white shadow-lg shadow-orange-600/20 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Tandai Perbaikan
                                            </button>
                                        </form>
                                    @endif

                                    @if($isNew || $isInProgress || $isUnderRepair)
                                        {{-- Selesai --}}
                                        <button type="button"
                                                @click="resolveId = {{ $report->id }}; resolveName = '{{ addslashes($report->facility->name ?? 'Fasilitas') }}'"
                                                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white shadow-lg shadow-teal-600/20 transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Selesai
                                        </button>

                                        {{-- Tolak --}}
                                        <button type="button"
                                                @click="rejectId = {{ $report->id }}; rejectName = '{{ addslashes($report->facility->name ?? 'Fasilitas') }}'"
                                                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-rose-600/20 text-rose-300 border border-rose-500/30 hover:bg-rose-600/30 hover:text-rose-200 transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Tolak
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $reports->links() }}
                </div>
            @else
                <div class="liquid-glass rounded-3xl border border-white/10 p-12 text-center shadow-2xl">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Tidak Ada Antrian Laporan</h3>
                    <p class="text-sm text-slate-400">Belum ada laporan kerusakan yang perlu diproses saat ini.</p>
                </div>
            @endif
        </div>

        {{-- Modal Selesai --}}
        <div x-show="resolveId !== null" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             @keydown.escape.window="resolveId = null">
            <div x-show="resolveId !== null"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="resolveId = null"
                 class="w-full max-w-md bg-slate-900 border border-white/15 rounded-3xl shadow-2xl p-6">
                <form method="POST" :action="'/staff/reports/' + resolveId + '/status'">
                    @csrf
                    <input type="hidden" name="status" value="resolved">
                    <div class="flex items-center gap-3 text-teal-400 mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Selesaikan Laporan</h3>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed mb-4">
                        Tandai laporan untuk fasilitas <span class="font-semibold text-white" x-text="resolveName"></span> sebagai selesai.
                        Fasilitas dapat diaktifkan kembali setelah perbaikan selesai.
                    </p>

                    <div class="space-y-2 mb-6">
                        <label for="resolution_note_resolve" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Catatan Resolusi <span class="text-teal-400">*</span>
                        </label>
                        <textarea name="resolution_note" id="resolution_note_resolve" rows="3" required minlength="3"
                                  placeholder="Jelaskan tindakan perbaikan yang telah dilakukan..."
                                  class="w-full text-sm rounded-2xl border-white/20 bg-white/5 text-white placeholder-slate-500 focus:border-teal-400 focus:ring-teal-400"></textarea>
                        <p class="text-[11px] text-slate-400">Wajib diisi, minimal 3 karakter.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="resolveId = null"
                                class="px-4 py-2 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-200 text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-teal-600/20 transition cursor-pointer">
                            Tandai Selesai
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Tolak --}}
        <div x-show="rejectId !== null" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             @keydown.escape.window="rejectId = null">
            <div x-show="rejectId !== null"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="rejectId = null"
                 class="w-full max-w-md bg-slate-900 border border-white/15 rounded-3xl shadow-2xl p-6">
                <form method="POST" :action="'/staff/reports/' + rejectId + '/status'">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <div class="flex items-center gap-3 text-rose-400 mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-rose-500/20 border border-rose-500/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Tolak Laporan</h3>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed mb-4">
                        Tolak laporan untuk fasilitas <span class="font-semibold text-white" x-text="rejectName"></span>.
                        Alasan penolakan akan dicatat dalam log.
                    </p>

                    <div class="space-y-2 mb-6">
                        <label for="resolution_note_reject" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Alasan Penolakan <span class="text-rose-400">*</span>
                        </label>
                        <textarea name="resolution_note" id="resolution_note_reject" rows="3" required minlength="3"
                                  placeholder="Jelaskan alasan penolakan laporan ini..."
                                  class="w-full text-sm rounded-2xl border-white/20 bg-white/5 text-white placeholder-slate-500 focus:border-rose-400 focus:ring-rose-400"></textarea>
                        <p class="text-[11px] text-slate-400">Wajib diisi, minimal 3 karakter.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="rejectId = null"
                                class="px-4 py-2 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-200 text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-500 hover:to-pink-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-rose-600/20 transition cursor-pointer">
                            Tolak Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
