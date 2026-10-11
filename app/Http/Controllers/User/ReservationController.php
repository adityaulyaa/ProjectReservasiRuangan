<?php

namespace App\Http\Controllers\User;

use App\Enums\FacilityStatus;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreReservationRequest;
use App\Models\Facility;
use App\Models\Reservation;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService
    ) {}

    /**
     * Tampilkan riwayat reservasi pengguna (SRS-10).
     */
    public function index(): View
    {
        $reservations = auth()->user()
            ->reservations()
            ->with('facility')
            ->latest('reservation_date')
            ->latest('start_time')
            ->paginate(10);

        return view('user.reservations.index', compact('reservations'));
    }

    /**
     * Tampilkan formulir pengajuan reservasi (SRS-09).
     */
    public function create(Request $request): View
    {
        $facilities = Facility::where('status', FacilityStatus::ACTIVE->value)
            ->orderBy('name')
            ->get();

        $availableSlots = $this->reservationService->slotsForDate();
        $slotMinutes = (int) config('reservation.slot_minutes', 30);

        $facilityId = $request->query('facility_id');
        $date = $request->query('date', now(config('app.timezone'))->toDateString());
        $validStartSlots = $this->validStartSlotsForDate($availableSlots, $date);
        $defaultStart = $validStartSlots[0] ?? ($availableSlots[0] ?? '07:00');
        $defaultEnd = Carbon::parse($defaultStart)->addMinutes($slotMinutes)->format('H:i');

        $startTime = $request->query('start_time');
        $endTime = $request->query('end_time');

        // Jika parameter slots dikirim dari availability page (format: "08:00,08:30,09:00")
        if ($request->filled('slots')) {
            $slotsArray = explode(',', (string) $request->query('slots'));
            sort($slotsArray);
            if (! empty($slotsArray)) {
                $startTime = $slotsArray[0];
                $lastSlot = end($slotsArray);
                $endTime = Carbon::parse($lastSlot)->addMinutes($slotMinutes)->format('H:i');
            }
        }

        $startTime = $startTime ?? $defaultStart;
        $endTime = $endTime ?? $defaultEnd;

        return view('user.reservations.create', compact(
            'facilities',
            'facilityId',
            'date',
            'startTime',
            'endTime',
            'availableSlots'
        ));
    }

    /**
     * Filter slot mulai agar tanggal hari ini tidak menawarkan jam yang sudah lewat.
     *
     * @param  array<int, string>  $slots
     * @return array<int, string>
     */
    private function validStartSlotsForDate(array $slots, string $date): array
    {
        $timezone = config('app.timezone');
        $today = now($timezone)->toDateString();

        if ($date !== $today) {
            return array_values($slots);
        }

        return array_values(array_filter($slots, function (string $slot) use ($today, $timezone): bool {
            return Carbon::parse($today.' '.$slot, $timezone)->greaterThan(now($timezone));
        }));
    }

    /**
     * Simpan pengajuan reservasi baru (SRS-09).
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $reservation = Reservation::create([
            'user_id' => auth()->id(),
            'facility_id' => $request->facility_id,
            'reservation_date' => $request->reservation_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'purpose' => $request->purpose,
            'status' => ReservationStatus::PENDING->value,
        ]);

        $this->reservationService->createLog(
            $reservation,
            'created',
            null,
            ReservationStatus::PENDING->value,
            'Pengajuan reservasi baru',
            auth()->id()
        );

        return redirect()->route('reservations.index')
            ->with('success', 'Reservasi berhasil diajukan dan sedang menunggu persetujuan.');
    }

    /**
     * Detail reservasi pengguna (SRS-10).
     */
    public function show($id): View
    {
        $reservation = Reservation::with(['facility', 'logs.actor'])->findOrFail((int) $id);

        $user = auth()->user();
        $role = is_object($user->role) ? $user->role->value : $user->role;
        if ($reservation->user_id !== $user->id && ! in_array($role, ['staff', 'admin'])) {
            abort(403, 'Anda tidak berhak melihat reservasi ini.');
        }

        // Cek apakah reservasi dapat dibatalkan (SRS-11)
        $statusValue = is_object($reservation->status) ? $reservation->status->value : $reservation->status;
        $canCancel = false;
        $cancelHours = (int) config('reservation.cancel_hours_before', 2);
        $timezone = config('app.timezone');

        if ($reservation->user_id === $user->id) {
            if (in_array($statusValue, ['pending', 'approved'])) {
                $resDate = $reservation->reservation_date instanceof \DateTimeInterface
                    ? $reservation->reservation_date->format('Y-m-d')
                    : substr((string) $reservation->reservation_date, 0, 10);
                $startDateTime = Carbon::parse($resDate.' '.$reservation->start_time, $timezone);

                if (now($timezone)->lessThan($startDateTime->copy()->subHours($cancelHours))) {
                    $canCancel = true;
                }
            }
        }

        return view('user.reservations.show', compact('reservation', 'canCancel', 'cancelHours'));
    }

    /**
     * Batalkan reservasi pengguna (SRS-11).
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $reservation = Reservation::findOrFail($id);

        // Hanya pemilik reservasi yang dapat membatalkannya
        abort_unless($reservation->user_id === auth()->id(), 403, 'Anda tidak berhak membatalkan reservasi ini.');

        // Hanya pending dan approved yang bisa dibatalkan
        $statusValue = is_object($reservation->status) ? $reservation->status->value : $reservation->status;
        if (! in_array($statusValue, ['pending', 'approved'])) {
            return back()->withErrors(['cancel_reason' => 'Reservasi dengan status ini tidak dapat dibatalkan.']);
        }

        // Cek batas waktu pembatalan (berlaku untuk pending dan approved)
        $cancelHours = (int) config('reservation.cancel_hours_before', 2);
        $timezone = config('app.timezone');
        $resDate = $reservation->reservation_date instanceof \DateTimeInterface
            ? $reservation->reservation_date->format('Y-m-d')
            : substr((string) $reservation->reservation_date, 0, 10);
        $startDateTime = Carbon::parse($resDate.' '.$reservation->start_time, $timezone);

        if (now($timezone)->greaterThanOrEqualTo($startDateTime->copy()->subHours($cancelHours))) {
            return back()->withErrors([
                'cancel_reason' => "Pembatalan reservasi hanya dapat dilakukan hingga {$cancelHours} jam sebelum waktu mulai. Saat ini sudah melewati batas waktu pembatalan.",
            ]);
        }

        $request->validate([
            'cancel_reason' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
            'cancel_reason.min' => 'Alasan pembatalan minimal 3 karakter.',
            'cancel_reason.max' => 'Alasan pembatalan maksimal 500 karakter.',
        ]);

        $oldStatus = $statusValue;
        $reservation->update([
            'status' => ReservationStatus::CANCELLED->value,
            'cancel_reason' => $request->cancel_reason,
        ]);

        $this->reservationService->createLog(
            $reservation,
            'cancelled',
            $oldStatus,
            ReservationStatus::CANCELLED->value,
            $request->cancel_reason,
            auth()->id()
        );

        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Reservasi berhasil dibatalkan.');
    }
}
