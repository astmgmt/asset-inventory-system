<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//ADDED FOR ROLE-BASED AUTHENTICATION
use Illuminate\Support\Facades\Auth;

//ADDED FOR LOGIN REQUEST VALIDATION
use Illuminate\Validation\ValidationException;

use App\Models\UserActivity;

class CustomAuthenticatedSessionController extends Controller
{
    public function store(Request $request)
    {
        // Validate required fields
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login_type = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $login_type => $request->input('login'),
            'password' => $request->input('password'),
        ];

        // Attempt to login using username or email
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        // Regenerate session
        $request->session()->regenerate();

        $user = Auth::user();

        // ADDED FOR REDIRECTION OF USER IF THEY ARE NOT YET APPROVED
        if ($user->status !== 'Approved') {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'login' => 'Your account status is "' . $user->status . '". You cannot login at this time.',
            ]);
        }

        // Record login activity
        UserActivity::create([
            'user_id' => $user->id,
            'activity_name' => 'login',
            'status' => 'active',
            'description' => 'You logged in at ' . now()->format('M d, Y h:i A'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

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

        if (Auth::check()) {
            UserActivity::create([
                'user_id' => Auth::id(),
                'activity_name' => 'logout',
                'status' => 'inactive',
                'description' => 'You logged out at ' . now()->format('M d, Y h:i A'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        }
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
