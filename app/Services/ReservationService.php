<?php

namespace App\Services;

use App\Enums\FacilityStatus;
use App\Enums\ReservationStatus;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\ReservationLog;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * Cek apakah ada bentrok dengan reservasi approved lain.
     */
    public function checkConflict(int $facilityId, string $date, string $start, string $end, ?int $excludeId = null): bool
    {
        $startTime = strlen($start) === 5 ? $start.':00' : $start;
        $endTime = strlen($end) === 5 ? $end.':00' : $end;

        $query = Reservation::where('facility_id', $facilityId)
            ->where('reservation_date', $date)
            ->where('status', ReservationStatus::APPROVED->value)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
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

        if ($startTime->minute % $slotMinutes !== 0 || $endTime->minute % $slotMinutes !== 0) {
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
     * Setujui reservasi (pending → approved).
     * Cek facility active & anti-bentrok sebelum approve.
     *
     * @throws ValidationException
     */
    public function approve(Reservation $reservation, int $actorId): void
    {
        if ($reservation->status !== ReservationStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Hanya reservasi berstatus menunggu yang dapat disetujui.',
            ]);
        }

        $facility = $reservation->facility;
        $facilityError = $this->checkAvailableFacility($facility);
        if ($facilityError) {
            throw ValidationException::withMessages([
                'facility' => $facilityError,
            ]);
        }

        $hasConflict = $this->checkConflict(
            $reservation->facility_id,
            $reservation->reservation_date instanceof \DateTimeInterface
                ? $reservation->reservation_date->format('Y-m-d')
                : (string) $reservation->reservation_date,
            $reservation->start_time,
            $reservation->end_time,
            $reservation->id,
        );

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'conflict' => 'Terjadi bentrok dengan reservasi lain yang sudah disetujui pada slot waktu yang sama.',
            ]);
        }

        $oldStatus = $reservation->status->value;
        $reservation->update([
            'status' => ReservationStatus::APPROVED->value,
            'processed_by' => $actorId,
        ]);

        $this->createLog($reservation, 'approved', $oldStatus, 'approved', 'Reservasi disetujui oleh petugas.', $actorId);
    }

    /**
     * Tolak reservasi (pending → rejected) dengan alasan wajib.
     *
     * @throws ValidationException
     */
    public function reject(Reservation $reservation, string $reason, int $actorId): void
    {
        if ($reservation->status !== ReservationStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Hanya reservasi berstatus menunggu yang dapat ditolak.',
            ]);
        }

        $oldStatus = $reservation->status->value;
        $reservation->update([
            'status' => ReservationStatus::REJECTED->value,
            'reject_reason' => $reason,
            'processed_by' => $actorId,
        ]);

        $this->createLog($reservation, 'rejected', $oldStatus, 'rejected', $reason, $actorId);
    }

    /**
     * Pembatalan darurat oleh petugas (approved → cancelled) dengan alasan wajib.
     *
     * @throws ValidationException
     */
    public function cancelForced(Reservation $reservation, string $reason, int $actorId): void
    {
        if ($reservation->status !== ReservationStatus::APPROVED) {
            throw ValidationException::withMessages([
                'status' => 'Pembatalan darurat hanya berlaku untuk reservasi yang sudah disetujui.',
            ]);
        }

        $oldStatus = $reservation->status->value;
        $reservation->update([
            'status' => ReservationStatus::CANCELLED->value,
            'cancel_reason' => $reason,
            'processed_by' => $actorId,
        ]);

        $this->createLog($reservation, 'cancelled_forced', $oldStatus, 'cancelled', $reason, $actorId);
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
