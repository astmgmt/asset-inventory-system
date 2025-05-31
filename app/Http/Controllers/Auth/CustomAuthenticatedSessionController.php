<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//ADDED FOR ROLE-BASED AUTHENTICATION
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Requests\LoginRequest;

class CustomAuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request)
    {
        $request->authenticate();  // Validates credentials
        $request->session()->regenerate();

        $user = Auth::user();

        // Role-based redirect
        if ($user->role->name === 'Super Admin') {
            return redirect()->route('dashboard.superadmin');
        } elseif ($user->role->name === 'Admin') {
            return redirect()->route('dashboard.admin');
        } else {
            return redirect()->route('dashboard.user');
        }
    }
    
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
