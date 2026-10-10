<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                    <span class="w-2.5 h-6 rounded-full bg-rose-500"></span>
                    {{ __('Riwayat & Status Laporan Kerusakan') }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Pantau status penanganan laporan kendala dan kerusakan fasilitas yang Anda ajukan.
                </p>
            </div>
            <div>
                <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-rose-500/25 transition cursor-pointer">
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

            <!-- Success Alert -->
            @if(session('success'))
                <div class="rounded-2xl bg-teal-500/15 border border-teal-500/30 p-4 text-sm text-teal-300 flex items-center gap-3 shadow-lg">
                    <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="liquid-glass rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10 flex items-center justify-between">
                    <h3 class="font-bold text-base text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Daftar Laporan Anda
                    </h3>
                </div>

                @if($reports->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-black/20 text-xs font-semibold uppercase text-slate-400 tracking-wider border-b border-white/10">
                                    <th class="py-4 px-6">Fasilitas / Ruangan</th>
                                    <th class="py-4 px-6">Kategori</th>
                                    <th class="py-4 px-6">Tanggal Lapor</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                                @foreach($reports as $report)
                                    @php
                                        $statusValue = is_object($report->status) ? $report->status->value : $report->status;
                                        $badgeClasses = [
                                            'new' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                            'in_progress' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                            'under_repair' => 'bg-orange-500/20 text-orange-300 border-orange-500/30',
                                            'resolved' => 'bg-teal-500/20 text-teal-300 border-teal-500/30',
                                            'rejected' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                        ][$statusValue] ?? 'bg-white/10 text-slate-300 border-white/20';

                                        $badgeLabels = [
                                            'new' => 'Baru',
                                            'in_progress' => 'Sedang Diproses',
                                            'under_repair' => 'Sedang Diperbaiki',
                                            'resolved' => 'Selesai',
                                            'rejected' => 'Ditolak',
                                        ][$statusValue] ?? ucfirst($statusValue);
                                    @endphp
                                    <tr class="hover:bg-white/5 transition">
                                        <td class="py-4 px-6 font-semibold text-white">
                                            {{ $report->facility->name ?? '-' }}
                                            <span class="block text-xs font-normal text-slate-400 mt-0.5">
                                                {{ $report->facility->location ?? '' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 font-medium text-amber-300 capitalize">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 text-xs">
                                                {{ $report->category }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap text-slate-300 text-xs">
                                            {{ $report->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClasses }}">
                                                {{ $badgeLabels }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 whitespace-nowrap text-right">
                                            <a href="{{ route('reports.show', $report->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-200 hover:text-white text-xs font-medium transition cursor-pointer">
                                                <span>Lihat Detail</span>
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
                    @if($reports->hasPages())
                        <div class="p-4 border-t border-white/10">
                            {{ $reports->links() }}
                        </div>
                    @endif
                @else
                    <div class="py-16 text-center text-slate-400">
                        <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-white">Belum ada laporan kerusakan</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Jika Anda menemukan kendala atau kerusakan fasilitas, silakan laporkan melalui tombol di bawah.</p>
                        <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 mt-5 bg-rose-500 hover:bg-rose-400 text-white text-xs font-semibold rounded-xl shadow-lg shadow-rose-500/20 transition cursor-pointer">
                            Buat Laporan Baru
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
