<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ReservationController;
use App\Http\Controllers\User\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/facilities', [PublicController::class, 'facilities'])->name('facilities.index');
Route::get('/facilities/{id}/availability', [PublicController::class, 'facilityAvailability'])->name('facilities.availability');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::middleware('role:user')->group(function () {
        Route::resource('reservations', ReservationController::class);
        Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        
        Route::resource('reports', ReportController::class);
    });
});

require __DIR__.'/auth.php';
