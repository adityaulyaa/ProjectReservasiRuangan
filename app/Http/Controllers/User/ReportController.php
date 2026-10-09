<?php

namespace App\Http\Controllers\User;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreReportRequest;
use App\Models\Facility;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(): View
    {
        $reports = auth()->user()->reports()
            ->with('facility')
            ->latest()
            ->paginate(10);

        return view('user.reports.index', compact('reports'));
    }

    public function create(): View
    {
        $facilities = Facility::whereIn('status', ['active', 'maintenance'])
            ->orderBy('name')
            ->get();

        $categories = config('reservation.report_categories', [
            'listrik', 'ac', 'furniture', 'plumbing', 'it', 'lainnya',
        ]);

        return view('user.reports.create', compact('facilities', 'categories'));
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $this->reportService->handlePhotoUpload($request->file('photo'));
        }

        $report = Report::create([
            'user_id' => auth()->id(),
            'facility_id' => $request->facility_id,
            'category' => $request->category,
            'description' => $request->description,
            'photo_path' => $photoPath,
            'status' => ReportStatus::NEW->value,
        ]);

        $this->reportService->createLog(
            $report,
            'created',
            null,
            ReportStatus::NEW->value,
            'Pengajuan laporan kerusakan',
            auth()->id()
        );

        return redirect()->route('reports.index')
            ->with('success', 'Laporan kerusakan berhasil dikirim dan menunggu pemrosesan petugas.');
    }

    public function show(Report $report): View
    {
        $role = is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role;

        abort_unless($report->user_id === auth()->id() || in_array($role, ['staff', 'admin']), 403);

        $report->load(['facility', 'user', 'processedBy', 'logs.actor']);

        $photoUrl = $report->photo_path ? Storage::disk('public')->url($report->photo_path) : null;

        return view('user.reports.show', compact('report', 'photoUrl'));
    }
}
