<?php

namespace App\Services;

use App\Enums\FacilityStatus;
use App\Models\Facility;
use Illuminate\Support\Str;

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
     * Tandai fasilitas berstatus 'maintenance' terkait laporan kerusakan.
     */
    public function markFacilityMaintenance(int $facilityId): void
    {
        Facility::where('id', $facilityId)->update([
            'status' => FacilityStatus::MAINTENANCE->value,
        ]);
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
