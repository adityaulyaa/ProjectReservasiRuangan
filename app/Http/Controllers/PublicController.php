<?php

namespace App\Http\Controllers;

use App\Enums\FacilityStatus;
use App\Models\Facility;
use Illuminate\Http\Request;
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

    public function facilityAvailability($id) {}
}
