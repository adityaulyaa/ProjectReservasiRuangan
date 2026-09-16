<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\ReservationController;
use App\Http\Controllers\Staff\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('reservations/queue', [ReservationController::class, 'queue'])->name('reservations.queue');
    Route::post('reservations/{id}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('reservations/{id}/reject', [ReservationController::class, 'reject'])->name('reservations.reject');
    Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    
    Route::get('reports/queue', [ReportController::class, 'queue'])->name('reports.queue');
    Route::get('reports/{id}', [ReportController::class, 'show'])->name('reports.show');
    Route::post('reports/{id}/status', [ReportController::class, 'updateStatus'])->name('reports.updateStatus');
    Route::post('reports/{id}/maintenance', [ReportController::class, 'markMaintenance'])->name('reports.markMaintenance');
    Route::post('reports/{id}/active', [ReportController::class, 'markActive'])->name('reports.markActive');
});
