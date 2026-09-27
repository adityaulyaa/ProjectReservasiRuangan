<?php

namespace App\Http\Controllers;

use App\Enums\FacilityStatus;
use App\Enums\ReservationStatus;
use App\Models\Facility;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function index(): View
    {
        $facilities = Facility::whereIn('status', ['active', 'maintenance'])
            ->withCount(['reservations' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->orderBy('reservations_count', 'desc')
            ->take(6)
            ->get();
        $facilityStatuses = FacilityStatus::cases();

        return view('public.index', compact('facilities', 'facilityStatuses'));
    }

    public function facilities(Request $request): View
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'capacity_min' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|numeric|min:0',
        ]);

        $search = $request->query('search');
        $type = $request->query('type');
        $location = $request->query('location');
        $capacityMin = $request->query('capacity_min', $request->query('capacity'));

        $facilities = Facility::whereIn('status', ['active', 'maintenance'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($location, function ($query, $location) {
                $query->where('location', 'like', "%{$location}%");
            })
            ->when($capacityMin, function ($query, $capacityMin) {
                $query->where('capacity', '>=', (int) $capacityMin);
            })
            ->orderBy('name')
            ->get();

        $types = Facility::whereIn('status', ['active', 'maintenance'])
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        $locations = Facility::whereIn('status', ['active', 'maintenance'])
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $facilityStatuses = FacilityStatus::cases();

        return view('public.facilities', compact('facilities', 'facilityStatuses', 'types', 'locations'));
    }

    public function facilityAvailability(Request $request, int|string $id): View
    {
        $facility = Facility::findOrFail($id);

        if ($facility->status === FacilityStatus::INACTIVE || $facility->status->value === 'inactive') {
            abort(404, 'Fasilitas tidak ditemukan atau sedang tidak aktif.');
        }

        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $date = $request->query('date', now()->toDateString());

        $approvedReservations = Reservation::where('facility_id', $facility->id)
            ->where('reservation_date', $date)
            ->where('status', ReservationStatus::APPROVED->value)
            ->get();

        $slotMinutes = (int) config('reservation.slot_minutes', 30);
        $slots = app(ReservationService::class)->slotsForDate();

        $slotStatuses = [];
        $bookedCount = 0;

        foreach ($slots as $slot) {
            $slotStart = Carbon::parse($slot)->format('H:i:s');
            $slotEnd = Carbon::parse($slot)->addMinutes($slotMinutes)->format('H:i:s');

            $isOccupied = $approvedReservations->contains(function ($res) use ($slotStart, $slotEnd) {
                $resStart = Carbon::parse($res->start_time)->format('H:i:s');
                $resEnd = Carbon::parse($res->end_time)->format('H:i:s');

                return $resStart < $slotEnd && $resEnd > $slotStart;
            });

            if ($isOccupied) {
                $bookedCount++;
            }

            $slotStatuses[] = [
                'start' => $slot,
                'end' => Carbon::parse($slot)->addMinutes($slotMinutes)->format('H:i'),
                'is_occupied' => $isOccupied,
            ];
        }

        $isMaintenance = ($facility->status === FacilityStatus::MAINTENANCE || $facility->status->value === 'maintenance');
        $availableCount = $isMaintenance ? 0 : (count($slots) - $bookedCount);

        return view('public.facility-availability', compact(
            'facility',
            'date',
            'slotStatuses',
            'isMaintenance',
            'bookedCount',
            'availableCount'
        ));
    }
}
