<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('facilities', FacilityController::class);
    
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::post('users/{id}/reject', [UserController::class, 'reject'])->name('users.reject');
    
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{id}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
});
