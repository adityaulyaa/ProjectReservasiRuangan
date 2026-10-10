<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FacilityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFacilityRequest;
use App\Http\Requests\Admin\UpdateFacilityRequest;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacilityController extends Controller
{
    /**
     * Tampilkan daftar master fasilitas dengan paginasi, pencarian, dan jumlah reservasi & laporan.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $statusFilter = $request->query('status');

        $query = Facility::withCount(['reservations', 'reports']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($statusFilter && in_array($statusFilter, ['active', 'maintenance', 'inactive'], true)) {
            $query->where('status', $statusFilter);
        }

        $facilities = $query->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Facility::count(),
            'active' => Facility::where('status', FacilityStatus::ACTIVE->value)->count(),
            'maintenance' => Facility::where('status', FacilityStatus::MAINTENANCE->value)->count(),
            'inactive' => Facility::where('status', FacilityStatus::INACTIVE->value)->count(),
        ];

        return view('admin.facilities.index', compact('facilities', 'search', 'statusFilter', 'stats'));
    }

    /**
     * Tampilkan formulir pembuatan fasilitas baru.
     */
    public function create(): View
    {
        $commonTypes = [
            'Ruang Kelas',
            'Ruang Rapat',
            'Laboratorium',
            'Auditorium',
            'Aula',
            'Meeting Room',
            'Study Room',
            'Collaboration Space',
            'Lapangan Olahraga',
            'Studio',
            'Alat & Fasilitas Khusus',
        ];

        return view('admin.facilities.create', compact('commonTypes'));
    }

    /**
     * Simpan data fasilitas baru.
     */
    public function store(StoreFacilityRequest $request): RedirectResponse
    {
        $facility = Facility::create($request->validated());

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', "Fasilitas '{$facility->name}' berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Tampilkan detail master fasilitas beserta riwayat reservasi & laporan terkait.
     */
    public function show(Facility $facility): View
    {
        $facility->loadCount(['reservations', 'reports']);

        $recentReservations = $facility->reservations()
            ->with('user')
            ->latest('reservation_date')
            ->latest('start_time')
            ->take(5)
            ->get();

        $recentReports = $facility->reports()
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.facilities.show', compact('facility', 'recentReservations', 'recentReports'));
    }

    /**
     * Tampilkan formulir edit fasilitas.
     */
    public function edit(Facility $facility): View
    {
        $commonTypes = [
            'Ruang Kelas',
            'Ruang Rapat',
            'Laboratorium',
            'Auditorium',
            'Aula',
            'Meeting Room',
            'Study Room',
            'Collaboration Space',
            'Lapangan Olahraga',
            'Studio',
            'Alat & Fasilitas Khusus',
        ];

        return view('admin.facilities.edit', compact('facility', 'commonTypes'));
    }

    /**
     * Perbarui data fasilitas.
     */
    public function update(UpdateFacilityRequest $request, Facility $facility): RedirectResponse
    {
        $facility->update($request->validated());

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', "Data fasilitas '{$facility->name}' berhasil diperbarui.");
    }

    /**
     * Hapus permanen fasilitas jika tidak ada relasi reservasi/laporan.
     * Jika memiliki relasi, ditolak dan diarahkan untuk nonaktifkan.
     */
    public function destroy(Facility $facility): RedirectResponse
    {
        $reservationsCount = $facility->reservations()->count();
        $reportsCount = $facility->reports()->count();

        if ($reservationsCount > 0 || $reportsCount > 0) {
            return back()->with('error', "Fasilitas '{$facility->name}' tidak dapat dihapus permanen karena memiliki {$reservationsCount} reservasi dan {$reportsCount} laporan terkait. Silakan nonaktifkan fasilitas sebagai gantinya.");
        }

        $facilityName = $facility->name;
        $facility->delete();

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', "Fasilitas '{$facilityName}' berhasil dihapus secara permanen.");
    }

    /**
     * Ubah status fasilitas secara cepat (misalnya Aktif <-> Nonaktif atau ke status tertentu).
     */
    public function toggleStatus(Request $request, Facility $facility): RedirectResponse
    {
        $requestedStatus = $request->input('status');

        if ($requestedStatus && in_array($requestedStatus, ['active', 'maintenance', 'inactive'], true)) {
            $newStatus = FacilityStatus::from($requestedStatus);
        } else {
            // Default toggle: jika inactive jadikan active, jika active/maintenance jadikan inactive
            $newStatus = ($facility->status === FacilityStatus::INACTIVE)
                ? FacilityStatus::ACTIVE
                : FacilityStatus::INACTIVE;
        }

        $facility->update(['status' => $newStatus]);

        return back()->with('success', "Status fasilitas '{$facility->name}' berhasil diubah menjadi {$newStatus->label()}.");
    }
}
