<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FacilityStatus;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today()->format('Y-m-d');

        $stats = [
            'total_facilities' => Facility::count(),
            'active_facilities' => Facility::where('status', FacilityStatus::ACTIVE->value)->count(),
            'maintenance_facilities' => Facility::where('status', FacilityStatus::MAINTENANCE->value)->count(),
            'inactive_facilities' => Facility::where('status', FacilityStatus::INACTIVE->value)->count(),
            'total_users' => User::count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'unverified_users' => User::where('is_verified', false)->count(),
            'total_reservations' => Reservation::count(),
            'reservations_today' => Reservation::where('reservation_date', $today)->count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'approved_reservations' => Reservation::where('status', 'approved')->count(),
            'rejected_reservations' => Reservation::where('status', 'rejected')->count(),
            'cancelled_reservations' => Reservation::where('status', 'cancelled')->count(),
            'total_reports' => Report::count(),
            'new_reports' => Report::where('status', 'new')->count(),
            'in_progress_reports' => Report::where('status', 'in_progress')->count(),
            'resolved_reports' => Report::where('status', 'resolved')->count(),
            'rejected_reports' => Report::where('status', 'rejected')->count(),
        ];

        $recentFacilities = Facility::withCount(['reservations', 'reports'])
            ->latest()
            ->take(5)
            ->get();

        $topFacilities = Facility::withCount(['reservations' => function ($query) {
            $query->where('status', 'approved');
        }])
            ->orderByDesc('reservations_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentFacilities', 'topFacilities'));
    }
}
