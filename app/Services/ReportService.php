<?php

namespace App\Services;

use App\Enums\FacilityStatus;
use App\Enums\ReportStatus;
use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportService
{
    /**
     * Simpan foto laporan ke disk public dan return path `reports/{name}`.
     */
    public function handlePhotoUpload(mixed $file): string
    {
        $extension = $file->extension();
        $name = Str::random(32).'.'.$extension;

        $file->storeAs('reports', $name, 'public');

        return 'reports/'.$name;
    }

    /**
     * Catat perubahan status laporan di report_logs.
     */
    public function createLog(Report $report, string $action, ?string $old = null, ?string $new = null, ?string $note = null, ?int $actorId = null): ReportLog
    {
        return ReportLog::create([
            'report_id' => $report->id,
            'actor_id' => $actorId,
            'action' => $action,
            'old_status' => $old,
            'new_status' => $new,
            'note' => $note,
        ]);
    }

    /**
     * Ubah status laporan sesuai transisi yang valid (SRS-15).
     * new → in_progress; new/in_progress → resolved|rejected (wajib note).
     * Note wajib ketika status tujuan resolved atau rejected.
     * Sinkronisasi status fasilitas dijalankan dalam satu transaksi database.
     *
     * @throws ValidationException
     */
    public function updateStatus(Report $report, ReportStatus $status, ?string $note, int $actorId): void
    {
        $current = $report->status;

        $allowed = [
            ReportStatus::NEW->value => [
                ReportStatus::IN_PROGRESS->value,
                ReportStatus::UNDER_REPAIR->value,
                ReportStatus::RESOLVED->value,
                ReportStatus::REJECTED->value,
            ],
            ReportStatus::IN_PROGRESS->value => [
                ReportStatus::UNDER_REPAIR->value,
                ReportStatus::RESOLVED->value,
                ReportStatus::REJECTED->value,
            ],
            ReportStatus::UNDER_REPAIR->value => [
                ReportStatus::RESOLVED->value,
                ReportStatus::REJECTED->value,
            ],
        ];

        if (! in_array($status->value, $allowed[$current->value] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => 'Perubahan status laporan tidak valid.',
            ]);
        }

        if (in_array($status->value, [ReportStatus::RESOLVED->value, ReportStatus::REJECTED->value], true) && blank($note)) {
            throw ValidationException::withMessages([
                'resolution_note' => 'Catatan resolusi wajib diisi untuk status selesai atau ditolak.',
            ]);
        }

        $oldStatus = $current->value;

        DB::transaction(function () use ($report, $status, $note, $actorId, $oldStatus) {
            $report->update([
                'status' => $status->value,
                'resolution_note' => $note,
                'processed_by' => $actorId,
            ]);

            // Sinkronisasi status fasilitas otomatis.
            // Hanya saat laporan selesai/ditolak: cek ulang apakah fasilitas
            // layak dikembalikan ke status tersedia.
            if (in_array($status, [ReportStatus::RESOLVED, ReportStatus::REJECTED], true)) {
                $this->syncFacilityAvailability($report->facility_id);
            }

            $action = match ($status) {
                ReportStatus::IN_PROGRESS => 'in_progress',
                ReportStatus::UNDER_REPAIR => 'under_repair',
                ReportStatus::RESOLVED => 'resolved',
                ReportStatus::REJECTED => 'rejected',
                default => 'status_changed',
            };

            $this->createLog($report, $action, $oldStatus, $status->value, $note, $actorId);
        });
    }

    /**
     * Sinkronkan status fasilitas berdasarkan seluruh laporan aktif yang tersisa.
     *
     * - Jika masih ada laporan aktif (new/in_progress/under_repair), fasilitas
     *   dipertahankan pada status maintenance (tidak boleh dipakai).
     * - Jika tidak ada laporan aktif, fasilitas layak dikembalikan ke active,
     *   kecuali admin telah menonaktifkannya secara permanen (inactive).
     */
    public function syncFacilityAvailability(int $facilityId): void
    {
        $facility = Facility::find($facilityId);

        if (! $facility) {
            return;
        }

        // Jangan pernah mengaktifkan fasilitas yang dinonaktifkan admin.
        if ($facility->status === FacilityStatus::INACTIVE) {
            return;
        }

        $hasActiveReport = Report::where('facility_id', $facilityId)
            ->whereIn('status', [
                ReportStatus::NEW->value,
                ReportStatus::IN_PROGRESS->value,
                ReportStatus::UNDER_REPAIR->value,
            ])
            ->exists();

        $targetStatus = $hasActiveReport
            ? FacilityStatus::MAINTENANCE
            : FacilityStatus::ACTIVE;

        if ($facility->status !== $targetStatus) {
            $facility->update(['status' => $targetStatus->value]);
        }
    }

    /**
     * Tandai fasilitas berstatus 'maintenance' terkait laporan kerusakan.
     * Juga ubah status laporan menjadi 'under_repair'.
     * Fasilitas yang dinonaktifkan admin tidak diubah statusnya.
     */
    public function markFacilityMaintenance(Report $report, int $actorId): void
    {
        DB::transaction(function () use ($report, $actorId) {
            $facility = Facility::find($report->facility_id);

            // Hormati status inactive yang ditetapkan admin.
            if ($facility && $facility->status !== FacilityStatus::INACTIVE) {
                $facility->update(['status' => FacilityStatus::MAINTENANCE->value]);
            }

            $oldStatus = $report->status->value;

            $report->update([
                'status' => ReportStatus::UNDER_REPAIR->value,
                'processed_by' => $actorId,
            ]);

            $this->createLog(
                $report,
                'maintenance_marked',
                $oldStatus,
                ReportStatus::UNDER_REPAIR->value,
                'Fasilitas ditandai sedang dalam perbaikan',
                $actorId
            );
        });
    }

    /**
     * Kembalikan fasilitas ke status 'active' setelah selesai diperbaiki.
     * Tidak akan mengaktifkan fasilitas yang masih memiliki laporan aktif
     * maupun fasilitas yang dinonaktifkan admin.
     */
    public function markFacilityActive(int $facilityId): void
    {
        DB::transaction(function () use ($facilityId) {
            $this->syncFacilityAvailability($facilityId);
        });
    }
}
