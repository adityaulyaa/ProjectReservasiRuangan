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
            ->orderBy('name')
            ->take(6)
            ->get();
        $facilityStatuses = FacilityStatus::cases();

        return view('public.index', compact('facilities', 'facilityStatuses'));
    }

    public function facilities(): View
    {
        $facilities = Facility::whereIn('status', ['active', 'maintenance'])
            ->orderBy('name')
            ->paginate(9);
        $facilityStatuses = FacilityStatus::cases();

        return view('public.facilities', compact('facilities', 'facilityStatuses'));
    }

    public function facilityAvailability($id) {}
}
