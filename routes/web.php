<?php

use Illuminate\Support\Facades\Route;

use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;

use App\Livewire\Dashboard\SuperAdminDashboard;
use App\Livewire\Dashboard\AdminDashboard;
use App\Livewire\Dashboard\UserDashboard;

use App\Livewire\SuperAdmin\ManageAccount;
use App\Livewire\SuperAdmin\EditUser;
use App\Livewire\SuperAdmin\ViewUser;

use App\Livewire\SuperAdmin\ManageAssets;
use App\Livewire\SuperAdmin\ManageSoftwares;

use App\Livewire\SuperAdmin\AssetAssignments; 
use App\Livewire\SuperAdmin\SoftwareAssignments; 

use App\Livewire\SuperAdmin\BorrowRequests; 
use App\Livewire\SuperAdmin\ApproveBorrowerRequests; 
use App\Livewire\SuperAdmin\ApproveReturnRequests; 
use App\Livewire\SuperAdmin\AssetDisposals; 
use App\Livewire\SuperAdmin\PrintAssets;
use App\Livewire\SuperAdmin\PrintSoftwares;

use App\Livewire\User\UserBorrowAsset; 
use App\Livewire\User\UserContainers;
use App\Livewire\User\UserBorrowTransactions; 
use App\Livewire\User\UserReturnTransactions; 
use App\Livewire\User\UserHistoryTransactions;

use App\Http\Controllers\SuperAdmin\AssetAssignmentPdfController; 
use App\Http\Controllers\SuperAdmin\SoftwareAssignmentPDFController;
use App\Http\Controllers\SuperAdmin\ApproveBorrowController;
use App\Http\Controllers\SuperAdmin\ReturnApprovalController;
use App\Http\Controllers\SuperAdmin\AssetPdfController;

use App\Livewire\AccountProfile\EditProfile;

use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;
use App\Http\Controllers\Auth\CustomRegisteredUserController;

use App\Http\Controllers\SuperAdmin\AccountController;
use App\Http\Controllers\SuperAdmin\UserController;

use App\Http\Controllers\User\ReturnController;


// TEST
use Illuminate\Support\Facades\Mail;
use App\Models\Asset;
use App\Services\SendEmail;

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
    Route::get('/dashboard/superadmin', SuperAdminDashboard::class)
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

    Route::get('/superadmin/manage/account/view/{id}', ViewUser::class)
        ->name('superadmin.manage.view_account')
        ->middleware('role:Super Admin');

    Route::get('/manage/assets', ManageAssets::class)
        ->name('manage.assets')
        ->middleware('role:Super Admin,Admin');

    Route::get('/manage/softwares', ManageSoftwares::class)
        ->name('manage.softwares')
        ->middleware('role:Super Admin,Admin');

    Route::get('/asset/assignment', AssetAssignments::class)
        ->name('asset.assignment')
        ->middleware('role:Super Admin,Admin');

    Route::get('/asset/assignment/pdf/{assignment}', [AssetAssignmentPdfController::class, 'generate'])
        ->name('asset.assignment.pdf')
        ->middleware('role:Super Admin,Admin');

    Route::get('/software/assignment', SoftwareAssignments::class)
        ->name('software.assignment')
        ->middleware('role:Super Admin,Admin');        

    Route::get('/software/assignment/pdf/{id}', [SoftwareAssignmentPDFController::class, 'generatePDF'])
        ->name('software.assignment.pdf')
        ->middleware('role:Super Admin, Admin');

    Route::get('/account/edit/profile', EditProfile::class)
        ->name('account.edit.profile')
        ->middleware('role:Super Admin,Admin,User'); // CAN BE USED BY ALL ROLES TO EDIT THEIR PROFILE

    //TO FINALIZE
    Route::get('/borrow/requests', BorrowRequests::class)
        ->name('borrow.request')
        ->middleware('role:Super Admin,Admin'); // ADMIN AND SUPER ADMIN ACCESS ONLY




    Route::get('/approve/requests', ApproveBorrowerRequests::class)
        ->name('approve.requests')
        ->middleware('role:Super Admin,Admin'); // ADMIN AND SUPER ADMIN ACCESS ONLY

    Route::get('/return-requests', [ReturnApprovalController::class, 'index'])
        ->name('admin.return-requests')
        ->middleware('role:Super Admin,Admin'); // ADMIN AND SUPER ADMIN ACCESS ONLY
    
    Route::post('/return-requests/approve/{returnCode}', [ReturnApprovalController::class, 'approve'])
        ->name('admin.return-approve')
        ->middleware('role:Super Admin,Admin'); // ADMIN AND SUPER ADMIN ACCESS ONLY

    Route::get('/approve/return', ApproveReturnRequests::class)
        ->name('approve.return')
        ->middleware('role:Super Admin,Admin'); // ADMIN AND SUPER ADMIN ACCESS ONLY
        
    Route::get('/asset/disposal', AssetDisposals::class)
            ->name('asset.disposal')
            ->middleware('role:Super Admin,Admin'); // ADMIN AND SUPER ADMIN ACCESS ONLY

    Route::get('/print/assets', PrintAssets::class)
            ->name('print.assets')
            ->middleware('role:Super Admin,Admin'); // ADMIN AND SUPER ADMIN ACCESS ONLY

    Route::get('/print/softwares', PrintSoftwares::class)
            ->name('print.softwares')
            ->middleware('role:Super Admin,Admin'); // ADMIN AND SUPER ADMIN ACCESS ONLY


            

    // GENERATE PRINTABLE QRCODE IN PDF
    // Single asset PDF
    Route::get('assets/{id}/pdf', [AssetPdfController::class, 'generate'])
        ->name('assets.pdf')
        ->where('id', '[0-9]+'); // Ensure ID is numeric

    // Batch assets PDF
    Route::get('assets/batch-pdf', [AssetPdfController::class, 'generateBatch'])
        ->name('assets.batch-pdf');

    // Corrected the parameter name to match controller expectation
    Route::get('/borrow-pdf/{borrow_code}', [ApproveBorrowController::class, 'generatePDF'])
        ->name('borrow.pdf');

    Route::get('/user/history', UserHistoryTransactions::class)
        ->name('user.history')
        ->middleware('role:User');

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
    Route::get('/dashboard/user', UserDashboard::class)
        ->name('dashboard.user')
        ->middleware('role:User');

    // BORROW ASSETS FROM USER (LIVEWIRE)
    Route::get('/user/borrow/assets', UserBorrowAsset::class)
        ->name('user.borrow.assets')
        ->middleware('role:User');

    Route::get('/user/transactions', UserContainers::class)
        ->name('user.transactions')
        ->middleware('role:User');

    // USER RETURN TRANSACTIONS
    Route::get('/user/return/transactions', UserReturnTransactions::class)
        ->name('user.return.transactions')
        ->middleware('role:User');

    // Add PDF route
    Route::get('/return-pdf/{returnCode}', function($returnCode) {
        $returnItems = AssetReturnItem::with(['borrowItem.asset', 'borrowItem.transaction'])
            ->where('return_code', $returnCode)
            ->get();
        
        if ($returnItems->isEmpty()) abort(404);

        $pdf = PDF::loadView('pdf.return-asset', [
            'returnCode' => $returnCode,
            'returnItems' => $returnItems,
            'user' => Auth::user(),
            'returnDate' => now()->format('M d, Y H:i')
        ]);

        return $pdf->stream("Return-$returnCode.pdf");
    })->name('return.pdf');


    // PRINT PDF FOR USER HISTORY TRANSACTIONS
    Route::get('/user/history/pdf/{id}', function ($id) {
        $history = App\Models\UserHistory::findOrFail($id);
        
        // Verify user owns this history record
        if ($history->user_id !== auth()->id()) {
            abort(403);
        }
        
        $pdf = PDF::loadView('pdf.borrow-history', [
            'history' => $history,
            'user' => auth()->user()
        ]);
        
        return $pdf->download("history-{$history->borrow_code}.pdf");
    })->middleware('auth')->name('user.history.pdf');   


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

