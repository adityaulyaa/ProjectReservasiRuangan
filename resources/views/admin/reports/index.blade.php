<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                    <span class="w-2.5 h-6 rounded-full bg-teal-400"></span>
                    {{ __('Rekap Okupansi & Frekuensi Kerusakan') }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Analisis data penggunaan fasilitas kampus, tingkat okupansi jam operasional, dan frekuensi kendala/kerusakan.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Tombol Export CSV --}}
                <a href="{{ route('admin.reports.export.csv', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-xs font-semibold rounded-2xl shadow-sm transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </a>

                {{-- Tombol Export Excel --}}
                <a href="{{ route('admin.reports.export.excel', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-500/20 hover:bg-teal-500/30 text-teal-300 border border-teal-500/40 text-xs font-semibold rounded-2xl shadow-sm transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel
                </a>

                {{-- Tombol Export PDF --}}
                <a href="{{ route('admin.reports.export.pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 text-xs font-semibold rounded-2xl shadow-sm transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Stat Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Facilities --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-5 shadow-xl">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-2xl bg-teal-500/20 text-teal-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-white">{{ $stats['total_facilities'] }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Fasilitas Dalam Rekap</div>
                </div>

                {{-- Total Reservasi Disetujui --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-5 shadow-xl">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-emerald-300">{{ $stats['total_approved_reservations'] }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Reservasi Disetujui</div>
                </div>

                {{-- Rata-rata Okupansi --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-5 shadow-xl">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-indigo-300">{{ $stats['average_occupancy'] }}%</div>
                    <div class="text-xs text-slate-400 mt-0.5">Rata-rata Tingkat Okupansi</div>
                </div>

                {{-- Total Laporan Kerusakan --}}
                <div class="liquid-glass rounded-3xl border border-white/10 p-5 shadow-xl">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-rose-300">{{ $stats['total_reports'] }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Total Laporan Kerusakan</div>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="liquid-glass rounded-3xl border border-white/10 p-5 shadow-xl">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    {{-- Tanggal Dari --}}
                    <div>
                        <label for="from" class="block text-xs font-semibold text-slate-400 mb-1.5">Dari Tanggal</label>
                        <input type="date"
                               id="from"
                               name="from"
                               value="{{ $from ?? '' }}"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/15 text-white text-xs focus:outline-hidden focus:border-teal-400 transition" />
                    </div>

                    {{-- Tanggal Sampai --}}
                    <div>
                        <label for="to" class="block text-xs font-semibold text-slate-400 mb-1.5">Sampai Tanggal</label>
                        <input type="date"
                               id="to"
                               name="to"
                               value="{{ $to ?? '' }}"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/15 text-white text-xs focus:outline-hidden focus:border-teal-400 transition" />
                    </div>

                    {{-- Fasilitas --}}
                    <div>
                        <label for="facility_id" class="block text-xs font-semibold text-slate-400 mb-1.5">Fasilitas</label>
                        <select id="facility_id"
                                name="facility_id"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/15 text-white text-xs focus:outline-hidden focus:border-teal-400 transition">
                            <option value="">Semua Fasilitas</option>
                            @foreach($facilitiesList as $fac)
                                <option value="{{ $fac->id }}" {{ (string)$facilityId === (string)$fac->id ? 'selected' : '' }}>
                                    {{ $fac->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lokasi --}}
                    <div>
                        <label for="location" class="block text-xs font-semibold text-slate-400 mb-1.5">Lokasi / Gedung</label>
                        <select id="location"
                                name="location"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/15 text-white text-xs focus:outline-hidden focus:border-teal-400 transition">
                            <option value="">Semua Lokasi</option>
                            @foreach($locationsList as $loc)
                                <option value="{{ $loc }}" {{ (string)$location === (string)$loc ? 'selected' : '' }}>
                                    {{ $loc }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white text-xs font-semibold shadow-md transition cursor-pointer text-center">
                            Terapkan Filter
                        </button>
                        @if($from || $to || $facilityId || $location)
                            <a href="{{ route('admin.reports.index') }}" class="py-2.5 px-3 rounded-xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white border border-white/10 text-xs transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Recap Table --}}
            <div class="liquid-glass rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                <div class="p-5 border-b border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-white text-base">Tabel Rekapitulasi Okupansi & Kerusakan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            @if($from && $to)
                                Periode: <span class="text-teal-300 font-semibold">{{ $from }}</span> s/d <span class="text-teal-300 font-semibold">{{ $to }}</span>
                            @else
                                Rentang analisis: <span class="text-slate-300 font-semibold">30 Hari Terakhir / Keseluruhan</span>
                            @endif
                            &bull; Jam Operasional: 07.00 - 20.00 WIB (13 Jam/Hari)
                        </p>
                    </div>
                </div>

                @if($recapData->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/10 bg-black/20 text-xs font-semibold uppercase text-slate-400 tracking-wider">
                                    <th class="py-4 px-6">Tipe Fasilitas</th>
                                    <th class="py-4 px-6">Nama Fasilitas</th>
                                    <th class="py-4 px-6">Lokasi</th>
                                    <th class="py-4 px-6 text-center">Kapasitas</th>
                                    <th class="py-4 px-6 text-center">Status</th>
                                    <th class="py-4 px-6 text-center">Reservasi Approved</th>
                                    <th class="py-4 px-6 text-center">Total Jam Pakai</th>
                                    <th class="py-4 px-6">Tingkat Okupansi</th>
                                    <th class="py-4 px-6 text-center">Laporan Kerusakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                                @foreach($recapData as $row)
                                    @php
                                        $badgeColor = match($row['status']) {
                                            'active' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                            'maintenance' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                            default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                        };
                                        $occRate = $row['occupancy_rate'];
                                        $occColor = $occRate >= 70 ? 'bg-emerald-400' : ($occRate >= 30 ? 'bg-teal-400' : 'bg-indigo-400');
                                    @endphp
                                    <tr class="hover:bg-white/[0.02] transition">
                                        <td class="py-4 px-6 text-xs font-medium text-slate-400">
                                            {{ $row['type'] }}
                                        </td>
                                        <td class="py-4 px-6 font-bold text-white">
                                            {{ $row['name'] }}
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-300">
                                            {{ $row['location'] }}
                                        </td>
                                        <td class="py-4 px-6 text-center text-xs text-slate-200">
                                            {{ $row['capacity'] }} org
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeColor }}">
                                                {{ $row['status_label'] }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="px-2.5 py-1 rounded-xl bg-teal-500/10 text-teal-300 border border-teal-500/20 text-xs font-bold">
                                                {{ $row['approved_reservations_count'] }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center text-xs font-medium text-slate-200">
                                            {{ $row['approved_hours'] }} Jam
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-2">
                                                <div class="w-20 bg-white/10 rounded-full h-2 overflow-hidden shrink-0">
                                                    <div class="{{ $occColor }} h-2 rounded-full" style="width: {{ $occRate }}%"></div>
                                                </div>
                                                <span class="text-xs font-bold text-white">{{ $occRate }}%</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="px-2.5 py-1 rounded-xl bg-rose-500/10 text-rose-300 border border-rose-500/20 text-xs font-bold">
                                                {{ $row['reports_count'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-black/30 border-t border-white/10 font-bold text-xs uppercase text-slate-300">
                                    <td colspan="5" class="py-4 px-6 text-right">TOTAL & RATA-RATA:</td>
                                    <td class="py-4 px-6 text-center text-teal-300 text-sm">{{ $stats['total_approved_reservations'] }}</td>
                                    <td class="py-4 px-6 text-center text-slate-200 text-sm">{{ round($recapData->sum('approved_hours'), 1) }} Jam</td>
                                    <td class="py-4 px-6 text-indigo-300 text-sm">{{ $stats['average_occupancy'] }}% Rata-rata</td>
                                    <td class="py-4 px-6 text-center text-rose-300 text-sm">{{ $stats['total_reports'] }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="py-16 text-center">
                        <p class="text-slate-400 text-sm">Tidak ada data fasilitas yang sesuai dengan kriteria filter.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
