<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;

// ADDED FOR ROLE-BASED ROUTE IMPORTS
use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;
use App\Livewire\Dashboard\SuperAdminDashboard;
use App\Livewire\Dashboard\AdminDashboard;
use App\Livewire\Dashboard\UserDashboard;

// DEFAULT LANDING PAGE
Route::get('/', function () {
    return view('auth.login'); 
});

// AUTH ROUTES
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', function () {
        return view('auth.login'); 
    })->name('login');
    
    // Register
    Route::get('/register', function () {
        return view('auth.register'); 
    })->name('register');
    
    // Password Reset
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    
    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');
    
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

// OVERRIDE FORTIFY AUTHENTICATION
Route::post('/login', [CustomAuthenticatedSessionController::class, 'store']);
Route::post('/logout', [CustomAuthenticatedSessionController::class, 'destroy'])->name('logout');

// AUTHENTICATED ROUTES
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard/superadmin', [SuperAdminDashboard::class, 'render'])->name('dashboard.superadmin');
    Route::get('/dashboard/admin', [AdminDashboard::class, 'render'])->name('dashboard.admin');
    Route::get('/dashboard/user', [UserDashboard::class, 'render'])->name('dashboard.user');
});