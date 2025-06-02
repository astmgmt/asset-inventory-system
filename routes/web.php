<?php

use Illuminate\Support\Facades\Route;

//ADDED FOR ROLE-BASED ROUTE IMPORTS
use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;
use App\Livewire\Dashboard\SuperAdminDashboard;
use App\Livewire\Dashboard\AdminDashboard;
use App\Livewire\Dashboard\UserDashboard;

Route::get('/', function () {
    return view('welcome');
});


// OVERRIDE FORTIFY ROUTES FOR AUTHENTICATION
Route::post('/login', [CustomAuthenticatedSessionController::class, 'store']);
Route::post('/logout', [CustomAuthenticatedSessionController::class, 'destroy'])->name('logout');

// ROUTES BEEN AUTHENTICATED BASED ON ROLES
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard/superadmin', [SuperAdminDashboard::class,'render'])->name('dashboard.superadmin');
    Route::get('/dashboard/admin', [AdminDashboard::class, 'render'])->name('dashboard.admin');
    Route::get('/dashboard/user', [UserDashboard::class, 'render'])->name('dashboard.user');
    

    // Route::get('/dashboard/superadmin', SuperAdminDashboard::class)->name('dashboard.superadmin');
    // Route::get('/dashboard/admin', AdminDashboard::class)->name('dashboard.admin');
    //Route::get('/dashboard/user', UserDashboard::class)->name('dashboard.user');
});



//HIDEN AS BACKUP CODE IN CASE OF ERROR
// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });
