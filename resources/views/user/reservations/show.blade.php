<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('reservations.index') }}" class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="font-bold text-2xl text-white tracking-tight">
                            Reservasi #{{ $reservation->id }}
                        </h2>
                        @php
                            $statusValue = is_object($reservation->status) ? $reservation->status->value : $reservation->status;
                            $badgeClasses = [
                                'pending' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                'approved' => 'bg-teal-500/20 text-teal-300 border-teal-500/30',
                                'rejected' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                'cancelled' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                            ][$statusValue] ?? 'bg-white/10 text-slate-300 border-white/20';

                            $badgeLabels = [
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan',
                            ][$statusValue] ?? ucfirst($statusValue);
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClasses }}">
                            {{ $badgeLabels }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        Diajukan pada {{ $reservation->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                    </p>
                </div>
            </div>

            @if($canCancel)
                <div class="flex items-center gap-3">
                    <button type="button" 
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-reservation-cancellation')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-rose-600/20 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batalkan Reservasi
                    </button>
                </div>
            @endif
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
                        Terjadi kesalahan pada tindakan Anda:
                    </div>
                    <ul class="list-disc list-inside pl-7 text-xs text-rose-300/90">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Info Banner jika Ditolak atau Dibatalkan --}}
            @if($statusValue === 'rejected')
                <div class="liquid-glass rounded-3xl border border-rose-500/30 bg-rose-950/30 p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-rose-200">Reservasi Ditolak oleh Petugas</h4>
                        <p class="text-xs text-rose-300/80 mt-1">
                            Alasan: <span class="font-medium text-white">{{ $reservation->reject_reason ?? 'Tidak ada catatan spesifik.' }}</span>
                        </p>
                    </div>
                </div>
            @elseif($statusValue === 'cancelled')
                <div class="liquid-glass rounded-3xl border border-slate-500/30 bg-slate-900/40 p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-white/10 text-slate-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">Reservasi Dibatalkan</h4>
                        <p class="text-xs text-slate-300 mt-1">
                            Alasan: <span class="font-medium text-amber-200">{{ $reservation->cancel_reason ?? 'Dibatalkan oleh pemohon.' }}</span>
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Kolom Kiri: Detail Reservasi & Fasilitas (2 spans) --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Card Rincian Reservasi --}}
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-5 shadow-2xl">
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Informasi Jadwal Penggunaan
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                                <span class="text-xs text-slate-400 font-medium block mb-1">Tanggal Penggunaan</span>
                                <div class="font-bold text-white text-base">
                                    {{ \Illuminate\Support\Carbon::parse($reservation->reservation_date)->locale('id')->translatedFormat('l, d F Y') }}
                                </div>
                            </div>
                            <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                                <span class="text-xs text-slate-400 font-medium block mb-1">Waktu / Sesi</span>
                                <div class="font-bold text-teal-300 text-base">
                                    {{ substr($reservation->start_time, 0, 5) }} – {{ substr($reservation->end_time, 0, 5) }} WIB
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-xs text-slate-400 font-medium block mb-1.5">Tujuan / Keperluan Penggunaan</span>
                            <div class="bg-white/5 rounded-2xl p-4 border border-white/10 text-sm text-slate-200 leading-relaxed whitespace-pre-line">
                                {{ $reservation->purpose }}
                            </div>
                        </div>
                    </div>

                    {{-- Card Info Fasilitas --}}
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-4 shadow-2xl">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Fasilitas yang Dipesan
                            </h3>
                            <a href="{{ route('facilities.availability', ['id' => $reservation->facility_id, 'date' => $reservation->reservation_date]) }}" 
                               target="_blank"
                               class="text-xs font-semibold text-teal-300 hover:text-teal-200 hover:underline flex items-center gap-1">
                                <span>Cek Kalender Fasilitas</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>

                        <div>
                            <h4 class="text-xl font-bold text-white">
                                {{ $reservation->facility->name ?? 'Fasilitas' }}
                            </h4>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-300 mt-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/10">
                                    <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    {{ $reservation->facility->type ?? '-' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/10">
                                    <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    {{ $reservation->facility->location ?? '-' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/10">
                                    <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Kapasitas: {{ $reservation->facility->capacity ?? '-' }} Orang
                                </span>
                            </div>
                        </div>

                        @if(!empty($reservation->facility->description))
                            <p class="text-xs text-slate-400 pt-3 border-t border-white/10">
                                {{ $reservation->facility->description }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Kolom Kanan: Log Aktivitas & Status Riwayat (1 span) --}}
                <div class="space-y-6">
                    <div class="liquid-glass rounded-3xl border border-white/10 p-6 sm:p-8 space-y-4 shadow-2xl">
                        <h3 class="text-base font-bold text-white border-b border-white/10 pb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Riwayat & Log Status
                        </h3>

                        @if($reservation->logs->count() > 0)
                            <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-white/20">
                                @foreach($reservation->logs as $log)
                                    <div class="relative group">
                                        {{-- Bullet circle --}}
                                        <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full bg-teal-400 ring-4 ring-slate-900 border-2 border-teal-500"></div>

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
                                                    Status: <span class="font-semibold text-teal-300">{{ ucfirst($log->new_status) }}</span>
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

    {{-- Modal Pembatalan Reservasi (SRS-11) --}}
    @if($canCancel)
        <x-modal name="confirm-reservation-cancellation" focusable>
            <form method="POST" action="{{ route('reservations.cancel', $reservation->id) }}" class="p-6 bg-slate-900 text-white rounded-3xl border border-white/15">
                @csrf

                <div class="flex items-center gap-3 text-rose-400 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/20 border border-rose-500/30 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Konfirmasi Pembatalan Reservasi
                    </h3>
                </div>

                <p class="text-sm text-slate-300 leading-relaxed mb-4">
                    Apakah Anda yakin ingin membatalkan pengajuan reservasi untuk fasilitas 
                    <span class="font-semibold text-white">{{ $reservation->facility->name }}</span> pada tanggal 
                    <span class="font-semibold text-white">{{ \Illuminate\Support\Carbon::parse($reservation->reservation_date)->locale('id')->translatedFormat('d M Y') }}</span> 
                    ({{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }} WIB)?
                    Slot waktu yang telah Anda pilih akan kembali tersedia untuk pengguna lain.
                </p>

                <div class="space-y-2 mb-6">
                    <label for="cancel_reason" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                        Alasan Pembatalan <span class="text-rose-400">*</span>
                    </label>
                    <textarea 
                        name="cancel_reason" 
                        id="cancel_reason" 
                        rows="3" 
                        required 
                        placeholder="Contoh: Rapat dibatalkan oleh pihak penyelenggara..." 
                        class="w-full text-sm rounded-2xl border-white/20 bg-white/5 text-white placeholder-slate-500 focus:border-rose-400 focus:ring-rose-400"></textarea>
                    <p class="text-[11px] text-slate-400">Minimal 3 karakter. Alasan ini akan tercatat dalam log riwayat.</p>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-200 text-sm font-semibold transition cursor-pointer">
                        Tutup
                    </button>

                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-rose-600/20 transition cursor-pointer">
                        Ya, Batalkan Reservasi
                    </button>
                </div>
            </form>
        </x-modal>
    @endif
</x-app-layout>
