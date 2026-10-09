<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-teal-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    Antrian Reservasi
                </h2>
                <p class="text-sm text-slate-400 mt-1 ml-[52px]">Kelola pengajuan dan persetujuan reservasi fasilitas</p>
            </div>

            {{-- Filter Status --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('staff.reservations.queue') }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition {{ !$statusFilter ? 'bg-teal-500/20 text-teal-300 border-teal-500/30' : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10 hover:text-white' }}">
                    Semua
                </a>
                <a href="{{ route('staff.reservations.queue', ['status' => 'pending']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition {{ $statusFilter === 'pending' ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10 hover:text-white' }}">
                    Menunggu
                </a>
                <a href="{{ route('staff.reservations.queue', ['status' => 'approved']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition {{ $statusFilter === 'approved' ? 'bg-teal-500/20 text-teal-300 border-teal-500/30' : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10 hover:text-white' }}">
                    Disetujui
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{ rejectId: null, cancelId: null, rejectName: '', cancelName: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-2xl bg-teal-500/15 border border-teal-500/30 p-4 text-sm text-teal-300 flex items-center gap-3 shadow-lg animate-fade-in">
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

            @if($reservations->count() > 0)
                {{-- Stats Bar --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @php
                        $pendingCount = $reservations->where('status.value', 'pending')->count();
                        $approvedCount = $reservations->where('status.value', 'approved')->count();
                        $conflictCount = $reservations->where('isConflict', true)->count();
                    @endphp
                    <div class="liquid-glass rounded-2xl border border-white/10 p-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-white">{{ $pendingCount }}</div>
                            <div class="text-[11px] text-slate-400 font-medium">Menunggu</div>
                        </div>
                    </div>
                    <div class="liquid-glass rounded-2xl border border-white/10 p-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-white">{{ $approvedCount }}</div>
                            <div class="text-[11px] text-slate-400 font-medium">Disetujui</div>
                        </div>
                    </div>
                    <div class="liquid-glass rounded-2xl border border-white/10 p-4 flex items-center gap-3 {{ $conflictCount > 0 ? 'border-rose-500/30' : '' }}">
                        <div class="w-9 h-9 rounded-xl {{ $conflictCount > 0 ? 'bg-rose-500/20 text-rose-400' : 'bg-white/10 text-slate-400' }} flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-white">{{ $conflictCount }}</div>
                            <div class="text-[11px] text-slate-400 font-medium">Bentrok</div>
                        </div>
                    </div>
                </div>

                {{-- Reservation Cards --}}
                <div class="space-y-4">
                    @foreach($reservations as $reservation)
                        @php
                            $statusValue = is_object($reservation->status) ? $reservation->status->value : $reservation->status;
                            $isPending = $statusValue === 'pending';
                            $isApproved = $statusValue === 'approved';
                            $badgeClasses = [
                                'pending' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                'approved' => 'bg-teal-500/20 text-teal-300 border-teal-500/30',
                            ][$statusValue] ?? 'bg-white/10 text-slate-300 border-white/20';
                            $badgeLabels = [
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                            ][$statusValue] ?? ucfirst($statusValue);
                        @endphp
                        <div class="liquid-glass rounded-3xl border {{ $reservation->isConflict ? 'border-rose-500/30 bg-rose-950/10' : 'border-white/10' }} p-5 sm:p-6 shadow-2xl transition hover:border-white/20">
                            <div class="flex flex-col lg:flex-row lg:items-start gap-5">
                                {{-- Left: Info --}}
                                <div class="flex-1 space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClasses }}">
                                            {{ $badgeLabels }}
                                        </span>
                                        <span class="text-xs text-slate-400">#{{ $reservation->id }}</span>
                                        @if($reservation->isConflict)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Bentrok
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Facility & Schedule Info --}}
                                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                        <div class="flex-1 space-y-2">
                                            <h4 class="text-base font-bold text-white">{{ $reservation->facility->name ?? 'Fasilitas' }}</h4>
                                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-300">
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/5 border border-white/10">
                                                    <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ \Illuminate\Support\Carbon::parse($reservation->reservation_date)->locale('id')->translatedFormat('l, d M Y') }}
                                                </span>
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/5 border border-white/10">
                                                    <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ substr($reservation->start_time, 0, 5) }} – {{ substr($reservation->end_time, 0, 5) }} WIB
                                                </span>
                                            </div>
                                        </div>

                                        {{-- User Info --}}
                                        <div class="sm:text-right">
                                            <span class="text-[11px] font-medium text-slate-400 block mb-1">Pemohon</span>
                                            <div class="flex sm:justify-end items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shadow">
                                                    {{ strtoupper(substr($reservation->user->name ?? '?', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-white">{{ $reservation->user->name ?? '-' }}</div>
                                                    <div class="text-[11px] text-slate-400">{{ $reservation->user->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Purpose --}}
                                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                                        <span class="text-[11px] font-medium text-slate-400 block mb-1">Tujuan Penggunaan</span>
                                        <p class="text-xs text-slate-200 leading-relaxed line-clamp-2">{{ $reservation->purpose }}</p>
                                    </div>

                                    @if($reservation->isConflict)
                                        <div class="flex items-center gap-2 bg-rose-500/10 border border-rose-500/20 rounded-xl px-3 py-2 text-xs text-rose-300">
                                            <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Terdapat bentrok jadwal dengan reservasi yang sudah disetujui pada slot waktu yang sama.
                                        </div>
                                    @endif
                                </div>

                                {{-- Right: Actions --}}
                                <div class="flex flex-row lg:flex-col items-stretch gap-2 lg:w-44 shrink-0">
                                    {{-- Detail link --}}
                                    <a href="{{ route('reservations.show', $reservation->id) }}"
                                       class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10 hover:text-white transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </a>

                                    @if($isPending)
                                        {{-- Approve --}}
                                        <form method="POST" action="{{ route('staff.reservations.approve', $reservation->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    {{ $reservation->isConflict ? 'disabled' : '' }}
                                                    class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition cursor-pointer
                                                           {{ $reservation->isConflict 
                                                               ? 'bg-slate-700/50 text-slate-500 border border-slate-600/30 cursor-not-allowed' 
                                                               : 'bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white shadow-lg shadow-teal-600/20' }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                {{ $reservation->isConflict ? 'Bentrok' : 'Setujui' }}
                                            </button>
                                        </form>

                                        {{-- Reject --}}
                                        <button type="button"
                                                @click="rejectId = {{ $reservation->id }}; rejectName = '{{ addslashes($reservation->facility->name ?? 'Fasilitas') }}'"
                                                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-rose-600/20 text-rose-300 border border-rose-500/30 hover:bg-rose-600/30 hover:text-rose-200 transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Tolak
                                        </button>
                                    @endif

                                    @if($isApproved)
                                        {{-- Cancel Forced --}}
                                        <button type="button"
                                                @click="cancelId = {{ $reservation->id }}; cancelName = '{{ addslashes($reservation->facility->name ?? 'Fasilitas') }}'"
                                                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-amber-600/20 text-amber-300 border border-amber-500/30 hover:bg-amber-600/30 hover:text-amber-200 transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            Batalkan Darurat
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $reservations->links() }}
                </div>

            @else
                <div class="liquid-glass rounded-3xl border border-white/10 p-12 text-center shadow-2xl">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Tidak Ada Antrian Reservasi</h3>
                    <p class="text-sm text-slate-400">Belum ada pengajuan reservasi yang perlu diproses saat ini.</p>
                </div>
            @endif
        </div>

        {{-- Modal Tolak Reservasi --}}
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
                <form method="POST" :action="'/staff/reservations/' + rejectId + '/reject'">
                    @csrf
                    <div class="flex items-center gap-3 text-rose-400 mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-rose-500/20 border border-rose-500/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Tolak Reservasi</h3>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed mb-4">
                        Anda akan <span class="font-semibold text-rose-300">menolak</span> reservasi untuk fasilitas
                        <span class="font-semibold text-white" x-text="rejectName"></span>.
                        Alasan penolakan akan dicatat dalam log.
                    </p>

                    <div class="space-y-2 mb-6">
                        <label for="reject_reason" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Alasan Penolakan <span class="text-rose-400">*</span>
                        </label>
                        <textarea name="reject_reason" id="reject_reason" rows="3" required minlength="3"
                                  placeholder="Jelaskan alasan penolakan reservasi ini..."
                                  class="w-full text-sm rounded-2xl border-white/20 bg-white/5 text-white placeholder-slate-500 focus:border-rose-400 focus:ring-rose-400"></textarea>
                        <p class="text-[11px] text-slate-400">Minimal 3 karakter.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="rejectId = null"
                                class="px-4 py-2 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-200 text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-500 hover:to-pink-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-rose-600/20 transition cursor-pointer">
                            Tolak Reservasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Batalkan Darurat --}}
        <div x-show="cancelId !== null" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             @keydown.escape.window="cancelId = null">
            <div x-show="cancelId !== null"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="cancelId = null"
                 class="w-full max-w-md bg-slate-900 border border-white/15 rounded-3xl shadow-2xl p-6">
                <form method="POST" :action="'/staff/reservations/' + cancelId + '/cancel'">
                    @csrf
                    <div class="flex items-center gap-3 text-amber-400 mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Pembatalan Darurat</h3>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed mb-4">
                        Anda akan <span class="font-semibold text-amber-300">membatalkan secara darurat</span> reservasi yang sudah disetujui untuk fasilitas
                        <span class="font-semibold text-white" x-text="cancelName"></span>.
                        Slot waktu yang terkait akan dibebaskan kembali.
                    </p>

                    <div class="space-y-2 mb-6">
                        <label for="cancel_reason" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Alasan Pembatalan Darurat <span class="text-amber-400">*</span>
                        </label>
                        <textarea name="cancel_reason" id="cancel_reason" rows="3" required minlength="3"
                                  placeholder="Jelaskan alasan pembatalan darurat reservasi ini..."
                                  class="w-full text-sm rounded-2xl border-white/20 bg-white/5 text-white placeholder-slate-500 focus:border-amber-400 focus:ring-amber-400"></textarea>
                        <p class="text-[11px] text-slate-400">Minimal 3 karakter. Pembatalan ini bersifat final.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="cancelId = null"
                                class="px-4 py-2 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-200 text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-amber-600/20 transition cursor-pointer">
                            Ya, Batalkan Darurat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
