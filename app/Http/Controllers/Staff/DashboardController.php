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

        return view('staff.dashboard', compact(
            'pendingReservations',
            'approvedReservations',
            'newReports',
            'inProgressReports',
        ));
    }
}
