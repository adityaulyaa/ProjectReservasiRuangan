<?php

namespace App\Services;

use App\Enums\FacilityStatus;
use App\Enums\ReportStatus;
use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportLog;
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
     * Otomatis mengaktifkan fasilitas jika tidak ada laporan under_repair lainnya.
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

        $report->update([
            'status' => $status->value,
            'resolution_note' => $note,
            'processed_by' => $actorId,
        ]);

        // Sinkronisasi status fasilitas otomatis
        if (in_array($status, [ReportStatus::RESOLVED, ReportStatus::REJECTED], true)) {
            // Cek apakah masih ada laporan under_repair lain untuk fasilitas yang sama
            $hasOtherUnderRepair = Report::where('facility_id', $report->facility_id)
                ->where('id', '!=', $report->id)
                ->where('status', ReportStatus::UNDER_REPAIR->value)
                ->exists();

            // Jika tidak ada lagi laporan under_repair, aktifkan fasilitas
            if (! $hasOtherUnderRepair) {
                Facility::where('id', $report->facility_id)->update([
                    'status' => FacilityStatus::ACTIVE->value,
                ]);
            }
        }

        $action = match ($status) {
            ReportStatus::IN_PROGRESS => 'in_progress',
            ReportStatus::UNDER_REPAIR => 'under_repair',
            ReportStatus::RESOLVED => 'resolved',
            ReportStatus::REJECTED => 'rejected',
            default => 'status_changed',
        };

        $this->createLog($report, $action, $oldStatus, $status->value, $note, $actorId);
    }

    /**
     * Tandai fasilitas berstatus 'maintenance' terkait laporan kerusakan.
     * Juga ubah status laporan menjadi 'under_repair'.
     */
    public function markFacilityMaintenance(Report $report, int $actorId): void
    {
        Facility::where('id', $report->facility_id)->update([
            'status' => FacilityStatus::MAINTENANCE->value,
        ]);

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
    }

    /**
     * Kembalikan fasilitas ke status 'active' setelah selesai diperbaiki.
     */
    public function markFacilityActive(int $facilityId): void
    {
        Facility::where('id', $facilityId)->update([
            'status' => FacilityStatus::ACTIVE->value,
        ]);
    }
}
