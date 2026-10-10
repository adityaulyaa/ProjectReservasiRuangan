<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FacilityStatus;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_facilities' => Facility::count(),
            'active_facilities' => Facility::where('status', FacilityStatus::ACTIVE->value)->count(),
            'maintenance_facilities' => Facility::where('status', FacilityStatus::MAINTENANCE->value)->count(),
            'inactive_facilities' => Facility::where('status', FacilityStatus::INACTIVE->value)->count(),
            'total_users' => User::count(),
            'unverified_users' => User::where('is_verified', false)->count(),
            'total_reservations' => Reservation::count(),
            'total_reports' => Report::count(),
        ];

        $recentFacilities = Facility::withCount(['reservations', 'reports'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentFacilities'));
    }
}
