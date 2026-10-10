<?php

namespace App\Http\Controllers\Staff;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    /**
     * Antrian laporan: new & in_progress & under_repair, dengan filter status opsional.
     */
    public function queue(Request $request): View
    {
        $statusFilter = $request->query('status');

        $query = Report::with(['facility', 'user'])
            ->whereIn('status', [ReportStatus::NEW->value, ReportStatus::IN_PROGRESS->value, ReportStatus::UNDER_REPAIR->value])
            ->latest();

        if ($statusFilter && in_array($statusFilter, [ReportStatus::NEW->value, ReportStatus::IN_PROGRESS->value, ReportStatus::UNDER_REPAIR->value], true)) {
            $query->where('status', $statusFilter);
        }

        $reports = $query->paginate(15)->withQueryString();

        return view('staff.reports.queue', compact('reports', 'statusFilter'));
    }

    /**
     * Detail laporan (foto + log).
     */
    public function show(int $id): View
    {
        $report = Report::with(['facility', 'user', 'processedBy', 'logs.actor'])->findOrFail($id);

        $photoUrl = $report->photo_path
            ? Storage::disk('public')->url($report->photo_path)
            : null;

        return view('staff.reports.show', compact('report', 'photoUrl'));
    }

    /**
     * Ubah status laporan (new → in_progress → resolved/rejected).
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:in_progress,resolved,rejected'],
            'resolution_note' => ['nullable', 'string', 'min:3', 'max:500'],
        ], [
            'status.required' => 'Status laporan wajib dipilih.',
            'status.in' => 'Status laporan tidak valid.',
            'resolution_note.min' => 'Catatan resolusi minimal 3 karakter.',
            'resolution_note.max' => 'Catatan resolusi maksimal 500 karakter.',
        ]);

        $report = Report::findOrFail($id);

        $this->reportService->updateStatus(
            $report,
            ReportStatus::from($validated['status']),
            $validated['resolution_note'] ?? null,
            auth()->id(),
        );

        return redirect()->route('staff.reports.queue')
            ->with('success', 'Status laporan berhasil diperbarui.');
    }

    /**
     * Tandai fasilitas terkait laporan berstatus maintenance dan ubah status laporan ke under_repair.
     */
    public function markMaintenance(int $id): RedirectResponse
    {
        $report = Report::findOrFail($id);

        $this->reportService->markFacilityMaintenance($report, auth()->id());

        return redirect()->route('staff.reports.queue')
            ->with('success', 'Fasilitas ditandai sedang dalam perbaikan dan status laporan diperbarui.');
    }

    /**
     * Kembalikan fasilitas terkait laporan ke status active.
     */
    public function markActive(int $id): RedirectResponse
    {
        $report = Report::findOrFail($id);

        $this->reportService->markFacilityActive($report->facility_id);

        return redirect()->route('staff.reports.queue')
            ->with('success', 'Fasilitas berhasil diaktifkan kembali.');
    }
}
