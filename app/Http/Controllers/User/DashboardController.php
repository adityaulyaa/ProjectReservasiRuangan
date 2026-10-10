<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $stats = [
            'total_reservations' => $user->reservations()->count(),
            'pending_reservations' => $user->reservations()->where('status', 'pending')->count(),
            'approved_reservations' => $user->reservations()->where('status', 'approved')->count(),
            'rejected_reservations' => $user->reservations()->where('status', 'rejected')->count(),
            'cancelled_reservations' => $user->reservations()->where('status', 'cancelled')->count(),
            'total_reports' => $user->reports()->count(),
            'new_reports' => $user->reports()->where('status', 'new')->count(),
            'in_progress_reports' => $user->reports()->where('status', 'in_progress')->count(),
            'resolved_reports' => $user->reports()->where('status', 'resolved')->count(),
            'rejected_reports' => $user->reports()->where('status', 'rejected')->count(),
        ];

        $recentReservations = $user->reservations()
            ->with('facility')
            ->latest('reservation_date')
            ->latest('start_time')
            ->take(5)
            ->get();

        $recentReports = $user->reports()
            ->with('facility')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentReservations', 'recentReports'));
    }
}
