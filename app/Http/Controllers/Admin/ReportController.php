<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman rekap okupansi fasilitas dan frekuensi kerusakan.
     */
    public function index(Request $request): View
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $facilityId = $request->query('facility_id');
        $location = $request->query('location');

        $recapData = $this->buildRecapData($from, $to, $facilityId, $location);

        $facilitiesList = Facility::orderBy('name')->get(['id', 'name']);
        $locationsList = Facility::select('location')->distinct()->orderBy('location')->pluck('location');

        $stats = [
            'total_facilities' => $recapData->count(),
            'total_approved_reservations' => $recapData->sum('approved_reservations_count'),
            'total_reports' => $recapData->sum('reports_count'),
            'average_occupancy' => $recapData->count() > 0 ? round($recapData->avg('occupancy_rate'), 1) : 0,
        ];

        return view('admin.reports.index', compact(
            'recapData',
            'stats',
            'facilitiesList',
            'locationsList',
            'from',
            'to',
            'facilityId',
            'location'
        ));
    }

    /**
     * Detail laporan jika diakses dari admin.
     */
    public function show($id)
    {
        // Redirect ke detail laporan umum/staff jika ada
        return redirect()->route('reports.show', $id);
    }

    /**
     * Export rekap universal (CSV, Excel, atau PDF berdasarkan parameter ?format=).
     */
    public function export(Request $request)
    {
        $format = strtolower((string) $request->query('format', 'csv'));

        return match ($format) {
            'pdf' => $this->exportPdf($request),
            'excel', 'xls', 'xlsx' => $this->exportExcel($request),
            default => $this->exportCsv($request),
        };
    }

    /**
     * Export Rekap dalam format CSV (StreamedResponse, text/csv, UTF-8 BOM).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $facilityId = $request->query('facility_id');
        $location = $request->query('location');

        $recapData = $this->buildRecapData($from, $to, $facilityId, $location);
        $dateStr = now()->format('Y-m-d');
        $filename = "rekap-{$dateStr}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($recapData) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM agar terbaca sempurna di Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header kolom CSV
            fputcsv($handle, [
                'Tipe Fasilitas',
                'Nama Fasilitas',
                'Lokasi',
                'Kapasitas',
                'Status',
                'Jumlah Reservasi Approved',
                'Total Jam Penggunaan',
                'Okupansi (%)',
                'Jumlah Laporan Kerusakan',
            ]);

            foreach ($recapData as $row) {
                fputcsv($handle, [
                    $row['type'],
                    $row['name'],
                    $row['location'],
                    $row['capacity'],
                    $row['status_label'],
                    $row['approved_reservations_count'],
                    $row['approved_hours'].' Jam',
                    $row['occupancy_rate'].'%',
                    $row['reports_count'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export Rekap dalam format Excel (.xls HTML table dengan UTF-8 BOM).
     */
    public function exportExcel(Request $request): Response
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $facilityId = $request->query('facility_id');
        $location = $request->query('location');

        $recapData = $this->buildRecapData($from, $to, $facilityId, $location);
        $dateStr = now()->format('Y-m-d');
        $filename = "rekap-{$dateStr}.xls";

        $viewContent = view('admin.reports.export-excel', [
            'recapData' => $recapData,
            'from' => $from,
            'to' => $to,
            'dateStr' => $dateStr,
        ])->render();

        // Tambahkan UTF-8 BOM di awal HTML table Excel
        $content = chr(0xEF).chr(0xBB).chr(0xBF).$viewContent;

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Export Rekap dalam format PDF (A4 Landscape via barryvdh/laravel-dompdf).
     */
    public function exportPdf(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $facilityId = $request->query('facility_id');
        $location = $request->query('location');

        $recapData = $this->buildRecapData($from, $to, $facilityId, $location);
        $dateStr = now()->format('Y-m-d');
        $filename = "rekap-{$dateStr}.pdf";

        $pdf = Pdf::loadView('admin.reports.export-pdf', [
            'recapData' => $recapData,
            'from' => $from,
            'to' => $to,
            'dateStr' => $dateStr,
            'adminName' => auth()->user()?->name ?? 'Administrator',
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    /**
     * Kalkulasi data rekap okupansi dan kerusakan fasilitas.
     */
    protected function buildRecapData(?string $from, ?string $to, ?string $facilityId, ?string $location): Collection
    {
        $facilityQuery = Facility::query();

        if ($facilityId) {
            $facilityQuery->where('id', $facilityId);
        }

        if ($location) {
            $facilityQuery->where('location', 'like', "%{$location}%");
        }

        $facilities = $facilityQuery->orderBy('type')->orderBy('name')->get();

        // Tentukan jumlah hari dalam rentang waktu operasional (07:00 - 20:00 = 13 jam / hari)
        if ($from && $to) {
            $daysCount = max(1, Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1);
        } else {
            $daysCount = 30; // Rentang default 30 hari
        }

        $totalOperationalHours = $daysCount * 13;

        return $facilities->map(function (Facility $facility) use ($from, $to, $totalOperationalHours) {
            // 1. Reservasi Approved
            $resQuery = $facility->reservations()->where('status', 'approved');

            if ($from) {
                $resQuery->whereDate('reservation_date', '>=', $from);
            }
            if ($to) {
                $resQuery->whereDate('reservation_date', '<=', $to);
            }

            $approvedReservations = $resQuery->get();
            $approvedCount = $approvedReservations->count();

            // Hitung total jam penggunaan dari start_time & end_time
            $totalMinutes = 0;
            foreach ($approvedReservations as $res) {
                $start = Carbon::parse($res->start_time);
                $end = Carbon::parse($res->end_time);
                $totalMinutes += max(0, $start->diffInMinutes($end));
            }

            $approvedHours = round($totalMinutes / 60, 1);
            $occupancyRate = $totalOperationalHours > 0
                ? min(100, round(($approvedHours / $totalOperationalHours) * 100, 1))
                : 0;

            // 2. Frekuensi Laporan Kerusakan
            $repQuery = $facility->reports();

            if ($from) {
                $repQuery->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $repQuery->whereDate('created_at', '<=', $to);
            }

            $reportsCount = $repQuery->count();

            $statusValue = is_object($facility->status) ? $facility->status->value : $facility->status;
            $statusLabel = is_object($facility->status) ? $facility->status->label() : ucfirst($facility->status);

            return [
                'id' => $facility->id,
                'type' => $facility->type,
                'name' => $facility->name,
                'location' => $facility->location,
                'capacity' => $facility->capacity,
                'status' => $statusValue,
                'status_label' => $statusLabel,
                'approved_reservations_count' => $approvedCount,
                'approved_hours' => $approvedHours,
                'occupancy_rate' => $occupancyRate,
                'reports_count' => $reportsCount,
            ];
        });
    }
}
