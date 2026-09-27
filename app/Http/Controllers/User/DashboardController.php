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
        ];

        $recentReservations = $user->reservations()
            ->with('facility')
            ->latest('reservation_date')
            ->latest('start_time')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentReservations'));
    }
}
