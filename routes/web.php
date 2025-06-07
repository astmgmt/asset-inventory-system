<?php

use Illuminate\Support\Facades\Route;

use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;

use App\Livewire\Dashboard\SuperAdminDashboard;
use App\Livewire\Dashboard\AdminDashboard;
use App\Livewire\Dashboard\UserDashboard;

use App\Livewire\SuperAdmin\ManageAccount;
use App\Livewire\SuperAdmin\EditUser;

use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;
use App\Http\Controllers\Auth\CustomRegisteredUserController;

use App\Http\Controllers\SuperAdmin\AccountController;
use App\Http\Controllers\SuperAdmin\UserController;

// DEFAULT LANDING PAGE
Route::get('/', function () {
    return view('auth.login'); 
});

// GUEST ROUTES
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login'); 
    })->name('login');
    
    Route::get('/register', function () {
        return view('auth.register'); 
    })->name('register');

    Route::post('/register', [CustomRegisteredUserController::class, 'store'])    
        ->name('register.store');
    
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
    // Super Admin Dashboard
    Route::get('/dashboard/superadmin', [SuperAdminDashboard::class, 'render'])
        ->name('dashboard.superadmin')
        ->middleware('role:Super Admin');

    Route::get('/dashboard/superadmin/create', [AccountController::class, 'showForm'])
        ->name('superadmin.register')
        ->middleware('role:Super Admin');

    Route::post('/dashboard/superadmin/create', [AccountController::class, 'create'])
        ->name('superadmin.create')
        ->middleware('role:Super Admin');



    // -------------------------------------------------------------------------------------------------

    // WORKING LIVEWIRE   

    Route::get('/superadmin/manage/account', ManageAccount::class)
        ->name('superadmin.manage')
        ->middleware('role:Super Admin');

    Route::get('/superadmin/manage/account/edit/{id}', EditUser::class)
        ->name('superadmin.manage.edit_account')
        ->middleware('role:Super Admin');

    
    // -------------------------------------------------------------------------------------------------




    // MANAGE ACCOUNTS - Approve, Edit, Delete and View Accounts   

    Route::post('/superadmin/manage/status/{user}', [UserController::class, 'updateStatus'])
        ->name('superadmin.manage.status')
        ->middleware('role:Super Admin');

    Route::get('/superadmin/manage/{user}', [UserController::class, 'show'])
        ->name('superadmin.manage.show')
        ->middleware('role:Super Admin');
        
    Route::get('/superadmin/manage/edit/{user}', [UserController::class, 'edit'])
        ->name('superadmin.manage.edit')
        ->middleware('role:Super Admin');

    Route::put('/superadmin/manage/{user}', [UserController::class, 'update'])
        ->name('superadmin.manage.update')
        ->middleware('role:Super Admin');
    
    Route::delete('/superadmin/manage/{user}', [UserController::class, 'destroy'])
        ->name('superadmin.manage.destroy')
        ->middleware('role:Super Admin');

    


    // Admin Dashboard
    Route::get('/dashboard/admin', [AdminDashboard::class, 'render'])
        ->name('dashboard.admin')
        ->middleware('role:Admin');
    
    // User Dashboard
    Route::get('/dashboard/user', [UserDashboard::class, 'render'])
        ->name('dashboard.user')
        ->middleware('role:User');
});

// TO CATCH ERRORS FROM DASHBOARD ROUTES
Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('Super Admin')) {
        return redirect()->route('dashboard.superadmin');
    } elseif (auth()->user()->hasRole('Admin')) {
        return redirect()->route('dashboard.admin');
    } else {
        return redirect()->route('dashboard.user');
    }
})->name('dashboard');
