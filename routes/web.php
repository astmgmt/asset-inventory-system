<?php

use Illuminate\Support\Facades\Route;

// ADDED FOR ROLE-BASED ROUTE IMPORTS
use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;
use App\Livewire\Dashboard\SuperAdminDashboard;
use App\Livewire\Dashboard\AdminDashboard;
use App\Livewire\Dashboard\UserDashboard;

// DEFAULT LANDING PAGE
Route::get('/', function () {
    return view('welcome');
});

// MANUAL LOGIN VIEW ROUTE (IF NOT AUTOMATICALLY HANDLED BY JETSTREAM)
Route::get('/login', function () {
    return view('auth.login'); // This should point to your login Blade file
})->middleware('guest')->name('login');

// OVERRIDE FORTIFY ROUTES FOR AUTHENTICATION
Route::post('/login', [CustomAuthenticatedSessionController::class, 'store']);
Route::post('/logout', [CustomAuthenticatedSessionController::class, 'destroy'])->name('logout');

// ROUTES AFTER AUTHENTICATION AND EMAIL VERIFICATION
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard/superadmin', [SuperAdminDashboard::class, 'render'])->name('dashboard.superadmin');
    Route::get('/dashboard/admin', [AdminDashboard::class, 'render'])->name('dashboard.admin');
    Route::get('/dashboard/user', [UserDashboard::class, 'render'])->name('dashboard.user');
});
