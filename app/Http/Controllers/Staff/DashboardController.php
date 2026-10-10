<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Reservation;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingReservations = Reservation::where('status', 'pending')->count();
        $approvedReservations = Reservation::where('status', 'approved')->count();
        $newReports = Report::where('status', 'new')->count();
        $inProgressReports = Report::where('status', 'in_progress')->count();

        $recentPendingReservations = Reservation::where('status', 'pending')
            ->with(['facility', 'user'])
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        $recentNewReports = Report::whereIn('status', ['new', 'in_progress'])
            ->with(['facility', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return view('staff.dashboard', compact(
            'pendingReservations',
            'approvedReservations',
            'newReports',
            'inProgressReports',
            'recentPendingReservations',
            'recentNewReports',
        ));
    }
}
