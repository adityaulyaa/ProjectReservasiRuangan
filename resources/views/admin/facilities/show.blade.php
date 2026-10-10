<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                    <span class="w-2.5 h-6 rounded-full bg-teal-400"></span>
                    {{ __('Detail Fasilitas: ') }} <span class="text-teal-300">{{ $facility->name }}</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $facility->type }} &bull; {{ $facility->location }} &bull; Kapasitas: {{ $facility->capacity }} orang
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('facilities.availability', $facility->id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-semibold text-teal-300 border border-white/10 transition inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Lihat Kalender Publik
                </a>
                <a href="{{ route('admin.facilities.edit', $facility) }}" class="px-4 py-2 rounded-xl bg-teal-500/15 hover:bg-teal-500/25 text-teal-300 border border-teal-500/30 text-xs font-semibold transition inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Fasilitas
                </a>
                <a href="{{ route('admin.facilities.index') }}" class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-semibold text-slate-300 border border-white/10 transition">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

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

            @php
                $statusVal = is_object($facility->status) ? $facility->status->value : $facility->status;
                $statusLabel = is_object($facility->status) ? $facility->status->label() : ucfirst($facility->status);
                $badgeClass = match($statusVal) {
                    'active' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                    'maintenance' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                    'inactive' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                    default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                };
                $hasRelations = ($facility->reservations_count > 0 || $facility->reports_count > 0);
            @endphp

            {{-- Main Info Card --}}
            <div class="liquid-glass rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-3">
                    {{-- Image / Visual --}}
                    <div class="h-64 md:h-auto bg-black/40 relative overflow-hidden flex items-center justify-center border-b md:border-b-0 md:border-r border-white/10">
                        @if($facility->image_url)
                            <img src="{{ $facility->image_url }}" alt="{{ $facility->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-center p-6 text-slate-500">
                                <svg class="w-16 h-16 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-xs font-semibold">Tidak ada foto</span>
                            </div>
                        @endif
                        <div class="absolute top-4 left-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border backdrop-blur-md {{ $badgeClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    {{-- Description & Metadata --}}
                    <div class="p-6 md:p-8 md:col-span-2 space-y-6">
                        <div>
                            <div class="text-xs uppercase font-bold tracking-wider text-teal-400 mb-1">{{ $facility->type }}</div>
                            <h3 class="text-2xl font-bold text-white">{{ $facility->name }}</h3>
                            <div class="text-sm text-slate-300 mt-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ $facility->location }}</span>
                                <span class="text-slate-600">&bull;</span>
                                <span>Kapasitas: <strong class="text-white">{{ $facility->capacity }}</strong> orang</span>
                            </div>
                        </div>

                        <div class="border-t border-white/10 pt-4">
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Deskripsi Fasilitas</div>
                            <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-line">
                                {{ $facility->description ?: 'Belum ada deskripsi rinci untuk fasilitas ini.' }}
                            </p>
                        </div>

                        {{-- Quick Controls Row --}}
                        <div class="border-t border-white/10 pt-4 flex flex-wrap items-center gap-3">
                            {{-- Toggle Status --}}
                            <form action="{{ route('admin.facilities.toggle-status', $facility) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                @if($statusVal === 'inactive')
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-xs font-semibold transition cursor-pointer">
                                        ✓ Aktifkan Fasilitas (Tampilkan ke Publik)
                                    </button>
                                @else
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 text-xs font-semibold transition cursor-pointer">
                                        ⚠ Nonaktifkan Fasilitas (Sembunyikan dari Publik)
                                    </button>
                                @endif
                            </form>

                            {{-- Delete if no relations --}}
                            @if(! $hasRelations)
                                <form action="{{ route('admin.facilities.destroy', $facility) }}"
                                      method="POST"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen fasilitas {{ addslashes($facility->name) }}? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 text-xs font-semibold transition cursor-pointer">
                                        Hapus Permanen
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-500 italic">
                                    (Terkunci dari penghapusan permanen karena memiliki data reservasi/laporan)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics Counters --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-xl flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Reservasi</div>
                        <div class="text-3xl font-extrabold text-teal-300 mt-1">{{ $facility->reservations_count }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Seluruh pemesanan ruangan ini</div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/20 text-teal-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-xl flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Laporan Kerusakan</div>
                        <div class="text-3xl font-extrabold text-rose-300 mt-1">{{ $facility->reports_count }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Keluhan atau laporan kendala</div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- 2 Columns: Recent Reservations & Recent Reports --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Recent Reservations --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-white/10 pb-3">
                        <h4 class="font-bold text-white text-base flex items-center gap-2">
                            <span class="w-2 h-4 rounded-full bg-teal-400"></span>
                            5 Reservasi Terakhir
                        </h4>
                    </div>

                    @if($recentReservations->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentReservations as $res)
                                @php
                                    $resStatus = is_object($res->status) ? $res->status->value : $res->status;
                                    $resBadge = match($resStatus) {
                                        'approved' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                        'pending' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                        'rejected' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                        default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                    };
                                @endphp
                                <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-semibold text-white">{{ $res->user->name ?? 'User #'.$res->user_id }}</div>
                                        <div class="text-slate-400 mt-0.5">
                                            {{ $res->reservation_date instanceof \DateTimeInterface ? $res->reservation_date->format('d M Y') : $res->reservation_date }}
                                            &bull; {{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 italic mt-0.5">{{ $res->purpose }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase {{ $resBadge }}">
                                            {{ $resStatus }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-500 text-xs">
                            Belum ada riwayat reservasi untuk fasilitas ini.
                        </div>
                    @endif
                </div>

                {{-- Recent Reports --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-white/10 pb-3">
                        <h4 class="font-bold text-white text-base flex items-center gap-2">
                            <span class="w-2 h-4 rounded-full bg-rose-400"></span>
                            5 Laporan Terakhir
                        </h4>
                    </div>

                    @if($recentReports->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentReports as $rep)
                                @php
                                    $repStatus = is_object($rep->status) ? $rep->status->value : $rep->status;
                                    $repBadge = match($repStatus) {
                                        'resolved' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                        'in_progress' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                        'new' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                        default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                    };
                                @endphp
                                <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-semibold text-white">Kategori: {{ ucfirst($rep->category) }}</div>
                                        <div class="text-slate-400 mt-0.5">
                                            Oleh: {{ $rep->user->name ?? 'User #'.$rep->user_id }} &bull; {{ $rep->created_at?->format('d M Y') }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 italic mt-0.5 line-clamp-1 max-w-xs">{{ $rep->description }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase {{ $repBadge }}">
                                            {{ $repStatus }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-500 text-xs">
                            Belum ada laporan kerusakan untuk fasilitas ini.
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
