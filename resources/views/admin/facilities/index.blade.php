<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-3">
                    <span class="w-2.5 h-6 rounded-full bg-teal-400"></span>
                    {{ __('Kelola Master Fasilitas') }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Tambah, ubah data, atur ketersediaan status, dan kelola seluruh fasilitas kampus.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.facilities.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-teal-500/20 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Fasilitas Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
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

            @if(session('error'))
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 text-sm text-rose-300 flex items-center gap-3 shadow-lg">
                    <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Stat Cards Mini --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="{{ route('admin.facilities.index') }}" class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-teal-500/40 transition">
                    <div class="text-xs text-slate-400 font-medium">Total Fasilitas</div>
                    <div class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</div>
                </a>
                <a href="{{ route('admin.facilities.index', ['status' => 'active']) }}" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 hover:border-emerald-500/40 transition">
                    <div class="text-xs text-emerald-400 font-medium">Aktif (Tampil Publik)</div>
                    <div class="text-2xl font-bold text-emerald-300 mt-1">{{ $stats['active'] }}</div>
                </a>
                <a href="{{ route('admin.facilities.index', ['status' => 'maintenance']) }}" class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 hover:border-amber-500/40 transition">
                    <div class="text-xs text-amber-400 font-medium">Dalam Perbaikan</div>
                    <div class="text-2xl font-bold text-amber-300 mt-1">{{ $stats['maintenance'] }}</div>
                </a>
                <a href="{{ route('admin.facilities.index', ['status' => 'inactive']) }}" class="p-4 rounded-2xl bg-slate-500/10 border border-slate-500/20 hover:border-slate-500/40 transition">
                    <div class="text-xs text-slate-400 font-medium">Nonaktif (Disembunyikan)</div>
                    <div class="text-2xl font-bold text-slate-300 mt-1">{{ $stats['inactive'] }}</div>
                </a>
            </div>

            {{-- Search & Filter Bar --}}
            <div class="liquid-glass rounded-3xl border border-white/10 p-5 shadow-xl">
                <form method="GET" action="{{ route('admin.facilities.index') }}" class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex-1 w-full relative">
                        <input type="text"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Cari berdasarkan nama fasilitas, tipe, atau lokasi..."
                               class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-white/5 border border-white/15 text-white placeholder-slate-400 text-sm focus:outline-hidden focus:border-teal-400 transition" />
                        <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <select name="status" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-white/15 text-white text-sm focus:outline-hidden focus:border-teal-400 transition">
                            <option value="">Semua Status</option>
                            <option value="active" {{ ($statusFilter ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="maintenance" {{ ($statusFilter ?? '') === 'maintenance' ? 'selected' : '' }}>Dalam Perbaikan</option>
                            <option value="inactive" {{ ($statusFilter ?? '') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>

                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-sm font-semibold text-white transition cursor-pointer">
                            Filter
                        </button>

                        @if($search || $statusFilter)
                            <a href="{{ route('admin.facilities.index') }}" class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs text-slate-400 hover:text-white transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Facilities Table --}}
            <div class="liquid-glass rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                @if($facilities->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/10 bg-black/20 text-xs font-semibold uppercase text-slate-400 tracking-wider">
                                    <th class="py-4 px-6">Fasilitas</th>
                                    <th class="py-4 px-6">Tipe & Lokasi</th>
                                    <th class="py-4 px-6">Kapasitas</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6 text-center">Reservasi</th>
                                    <th class="py-4 px-6 text-center">Laporan</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                                @foreach($facilities as $facility)
                                    @php
                                        $statusValue = is_object($facility->status) ? $facility->status->value : $facility->status;
                                        $statusLabel = is_object($facility->status) ? $facility->status->label() : ucfirst($facility->status);
                                        $badgeClass = match($statusValue) {
                                            'active' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                            'maintenance' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                            'inactive' => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                            default => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                                        };
                                        $hasRelations = ($facility->reservations_count > 0 || $facility->reports_count > 0);
                                    @endphp
                                    <tr class="hover:bg-white/[0.02] transition">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                @if($facility->image_url)
                                                    <img src="{{ $facility->image_url }}" alt="{{ $facility->name }}" class="w-12 h-12 rounded-2xl object-cover border border-white/10 shrink-0" onerror="this.style.display='none'">
                                                @else
                                                    <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 shrink-0 font-bold">
                                                        {{ substr($facility->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <a href="{{ route('admin.facilities.show', $facility) }}" class="font-bold text-white hover:text-teal-300 transition">
                                                        {{ $facility->name }}
                                                    </a>
                                                    <div class="text-xs text-slate-400 mt-0.5 line-clamp-1 max-w-xs">
                                                        {{ $facility->description ?: 'Tidak ada deskripsi' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="font-medium text-white text-xs">{{ $facility->type }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ $facility->location }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-200">
                                            <span class="font-semibold">{{ $facility->capacity }}</span> orang
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center text-xs">
                                            <span class="px-2 py-0.5 rounded-lg bg-teal-500/10 text-teal-300 border border-teal-500/20 font-bold">
                                                {{ $facility->reservations_count }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center text-xs">
                                            <span class="px-2 py-0.5 rounded-lg bg-rose-500/10 text-rose-300 border border-rose-500/20 font-bold">
                                                {{ $facility->reports_count }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                {{-- Show Detail --}}
                                                <a href="{{ route('admin.facilities.show', $facility) }}"
                                                   title="Lihat Detail"
                                                   class="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>

                                                {{-- Edit --}}
                                                <a href="{{ route('admin.facilities.edit', $facility) }}"
                                                   title="Edit Fasilitas"
                                                   class="p-2 rounded-xl bg-teal-500/10 hover:bg-teal-500/20 text-teal-300 border border-teal-500/20 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>

                                                {{-- Toggle Status (Nonaktifkan / Aktifkan) --}}
                                                <form action="{{ route('admin.facilities.toggle-status', $facility) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    @if($statusValue === 'inactive')
                                                        <button type="submit"
                                                                title="Aktifkan Fasilitas"
                                                                class="px-2.5 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 text-xs font-semibold transition cursor-pointer">
                                                            Aktifkan
                                                        </button>
                                                    @else
                                                        <button type="submit"
                                                                title="Nonaktifkan Fasilitas"
                                                                class="px-2.5 py-1.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 text-xs font-semibold transition cursor-pointer">
                                                            Nonaktifkan
                                                        </button>
                                                    @endif
                                                </form>

                                                {{-- Delete (Hard Delete if no relations) --}}
                                                @if(! $hasRelations)
                                                    <form action="{{ route('admin.facilities.destroy', $facility) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen fasilitas {{ addslashes($facility->name) }}? Tindakan ini tidak dapat dibatalkan.');"
                                                          class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                title="Hapus Permanen"
                                                                class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/20 transition cursor-pointer">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button"
                                                            disabled
                                                            title="Tidak dapat dihapus: Fasilitas memiliki riwayat reservasi atau laporan. Nonaktifkan saja fasilitas ini."
                                                            class="p-2 rounded-xl bg-white/5 text-slate-600 border border-white/5 cursor-not-allowed">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-white/10">
                        {{ $facilities->links() }}
                    </div>
                @else
                    <div class="py-16 text-center">
                        <div class="w-16 h-16 rounded-3xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-white">Tidak Ada Fasilitas Ditemukan</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                            @if($search || $statusFilter)
                                Tidak ada fasilitas yang cocok dengan filter pencarian. Coba ubah kata kunci atau reset filter.
                            @else
                                Belum ada fasilitas yang didaftarkan dalam sistem. Silakan tambahkan fasilitas baru.
                            @endif
                        </p>
                        @if($search || $statusFilter)
                            <a href="{{ route('admin.facilities.index') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-semibold text-white transition">
                                Reset Filter
                            </a>
                        @else
                            <a href="{{ route('admin.facilities.create') }}" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-2xl bg-teal-500 hover:bg-teal-400 text-xs font-semibold text-white transition">
                                Tambah Fasilitas Baru
                            </a>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
