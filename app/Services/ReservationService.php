<?php

namespace App\Services;

use App\Enums\FacilityStatus;
use App\Enums\ReservationStatus;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\ReservationLog;
use Illuminate\Support\Carbon;

class ReservationService
{
    /**
     * Cek apakah ada bentrok dengan reservasi approved lain.
     */
    public function checkConflict(int $facilityId, string $date, string $start, string $end, ?int $excludeId = null): bool
    {
        $query = Reservation::where('facility_id', $facilityId)
            ->where('reservation_date', $date)
            ->where('status', ReservationStatus::APPROVED->value)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
            });

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Validasi slot waktu terhadap jam operasional & kelipatan 30 menit.
     *
     * @return array<string>
     */
    public function validateTimeSlot(string $start, string $end): array
    {
        $errors = [];

        $slotMinutes = (int) config('reservation.slot_minutes');
        $open = (string) config('reservation.open_time');
        $close = (string) config('reservation.close_time');

        $startTime = Carbon::parse($start);
        $endTime = Carbon::parse($end);

        if (! $startTime->lt($endTime)) {
            $errors[] = 'start_time harus lebih kecil daripada end_time.';
        }

        if ($startTime->lt(Carbon::parse($open)) || $endTime->gt(Carbon::parse($close))) {
            $errors[] = "Slot waktu wajib berada dalam jam operasional ({$open}–{$close}).";
        }

        if ($startTime->minute() % $slotMinutes !== 0 || $endTime->minute() % $slotMinutes !== 0) {
            $errors[] = "Slot waktu wajib kelipatan {$slotMinutes} menit.";
        }

        return $errors;
    }

    /**
     * Cek facade fasilitas siap untuk disekir (status active).
     */
    public function checkAvailableFacility(Facility $facility): ?string
    {
        if ($facility->status->value !== FacilityStatus::ACTIVE->value) {
            return 'Fasilitas sedang tidak aktif atau dalam perbaikan.';
        }

        return null;
    }

    /**
     * Catat perubahan status reservasi di reservation_logs.
     */
    public function createLog(Reservation $reservation, string $action, ?string $old = null, ?string $new = null, ?string $note = null, ?int $actorId = null): void
    {
        ReservationLog::create([
            'reservation_id' => $reservation->id,
            'actor_id' => $actorId,
            'action' => $action,
            'old_status' => $old,
            'new_status' => $new,
            'note' => $note,
        ]);
    }

    /**
     * Daftar slot waktu tersedia per hari (07:00, 07:30, ..., 19:30).
     *
     * @return array<string>
     */
    public function slotsForDate(): array
    {
        $slots = [];

        $slotMinutes = (int) config('reservation.slot_minutes');
        $start = (string) config('reservation.open_time');
        $end = (string) config('reservation.close_time');

        $current = Carbon::parse($start);
        $close = Carbon::parse($end);

        while ($current->lt($close)) {
            $slots[] = $current->format('H:i');
            $current = $current->addMinutes($slotMinutes);
        }

        return $slots;
    }
}
