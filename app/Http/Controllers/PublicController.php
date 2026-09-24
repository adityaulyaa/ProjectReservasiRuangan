<?php

namespace App\Http\Controllers;

use App\Enums\FacilityStatus;
use App\Models\Facility;
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

    public function facilities(): View
    {
        $facilities = Facility::whereIn('status', ['active', 'maintenance'])
            ->orderBy('name')
            ->get();
        $facilityStatuses = FacilityStatus::cases();

        return view('public.facilities', compact('facilities', 'facilityStatuses'));
    }

    public function facilityAvailability($id) {}
}
