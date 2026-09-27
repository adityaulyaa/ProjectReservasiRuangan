<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                <span class="w-2.5 h-6 rounded-full bg-teal-400"></span>
                {{ __('Riwayat Reservasi Saya') }}
            </h2>
            <a href="{{ route('reservations.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-teal-500/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Reservasi Baru
            </a>
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

            @if(session('error'))
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 text-sm text-rose-300 flex items-center gap-3 shadow-lg">
                    <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="liquid-glass rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                @if($reservations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/10 bg-black/20 text-xs font-semibold uppercase text-slate-400 tracking-wider">
                                    <th class="py-4 px-6">ID</th>
                                    <th class="py-4 px-6">Fasilitas / Ruangan</th>
                                    <th class="py-4 px-6">Tanggal</th>
                                    <th class="py-4 px-6">Waktu</th>
                                    <th class="py-4 px-6">Tujuan</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                                @foreach($reservations as $res)
                                    @php
                                        $statusValue = is_object($res->status) ? $res->status->value : $res->status;
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
                                    <tr class="hover:bg-white/5 transition">
                                        <td class="py-4 px-6 font-semibold text-white">
                                            #{{ $res->id }}
                                        </td>
                                        <td class="py-4 px-6 font-medium text-white">
                                            {{ $res->facility->name ?? '-' }}
                                            <span class="block text-xs text-slate-400 font-normal mt-0.5">
                                                {{ $res->facility->location ?? '' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap text-slate-300">
                                            {{ \Illuminate\Support\Carbon::parse($res->reservation_date)->locale('id')->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap font-medium text-teal-300">
                                            {{ substr($res->start_time, 0, 5) }} – {{ substr($res->end_time, 0, 5) }}
                                        </td>
                                        <td class="py-4 px-6 max-w-xs truncate text-slate-300" title="{{ $res->purpose }}">
                                            {{ $res->purpose }}
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

                    @if($reservations->hasPages())
                        <div class="p-4 border-t border-white/10">
                            {{ $reservations->links() }}
                        </div>
                    @endif
                @else
                    <div class="py-16 text-center text-slate-400">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-teal-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-white mb-1">Belum Ada Reservasi</h4>
                        <p class="text-sm text-slate-400 max-w-sm mx-auto mb-6">
                            Anda belum pernah mengajukan reservasi fasilitas. Silakan ajukan jadwal reservasi baru.
                        </p>
                        <a href="{{ route('reservations.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-500 hover:bg-teal-400 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-teal-500/20 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Ajukan Reservasi Sekarang
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
