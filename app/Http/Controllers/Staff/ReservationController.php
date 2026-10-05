<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService,
    ) {}

    /**
     * Antrian reservasi: pending + approved, ordered by date & time.
     */
    public function queue(Request $request)
    {
        $statusFilter = $request->query('status');

        $query = Reservation::with(['facility', 'user'])
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('reservation_date')
            ->orderBy('start_time');

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved'])) {
            $query->where('status', $statusFilter);
        }

        $reservations = $query->paginate(15)->withQueryString();

        // Flag setiap reservasi pending apakah bentrok dengan approved lain
        foreach ($reservations as $reservation) {
            $reservation->isConflict = false;
            if ($reservation->status->value === 'pending') {
                $reservation->isConflict = $this->reservationService->checkConflict(
                    $reservation->facility_id,
                    $reservation->reservation_date instanceof \DateTimeInterface
                        ? $reservation->reservation_date->format('Y-m-d')
                        : (string) $reservation->reservation_date,
                    $reservation->start_time,
                    $reservation->end_time,
                    $reservation->id,
                );
            }
        }

        return view('staff.reservations.queue', compact('reservations', 'statusFilter'));
    }

    /**
     * Setujui reservasi.
     */
    public function approve($id)
    {
        $reservation = Reservation::with('facility')->findOrFail($id);

        $this->reservationService->approve($reservation, auth()->id());

        return redirect()->route('staff.reservations.queue')
            ->with('success', 'Reservasi berhasil disetujui.');
    }

    /**
     * Tolak reservasi (wajib alasan).
     */
    public function reject($id, Request $request)
    {
        $request->validate([
            'reject_reason' => 'required|string|min:3|max:500',
        ], [
            'reject_reason.required' => 'Alasan penolakan wajib diisi.',
            'reject_reason.min' => 'Alasan penolakan minimal 3 karakter.',
        ]);

        $reservation = Reservation::findOrFail($id);

        $this->reservationService->reject($reservation, $request->reject_reason, auth()->id());

        return redirect()->route('staff.reservations.queue')
            ->with('success', 'Reservasi berhasil ditolak.');
    }

    /**
     * Batalkan darurat (hanya approved, wajib alasan).
     */
    public function cancel($id, Request $request)
    {
        $request->validate([
            'cancel_reason' => 'required|string|min:3|max:500',
        ], [
            'cancel_reason.required' => 'Alasan pembatalan darurat wajib diisi.',
            'cancel_reason.min' => 'Alasan pembatalan minimal 3 karakter.',
        ]);

        $reservation = Reservation::findOrFail($id);

        $this->reservationService->cancelForced($reservation, $request->cancel_reason, auth()->id());

        return redirect()->route('staff.reservations.queue')
            ->with('success', 'Reservasi berhasil dibatalkan secara darurat.');
    }
}
