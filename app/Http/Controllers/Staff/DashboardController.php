<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Report;

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
