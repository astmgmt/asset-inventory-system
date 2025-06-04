<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();
        
        // Check if user has any of the required roles
        if ($user && $user->role && in_array($user->role->name, $roles)) {
            return $next($request);
        }

        // Redirect to appropriate dashboard based on user role
        if ($user) {
            if ($user->isSuperAdmin()) {
                return redirect()->route('dashboard.superadmin');
            } elseif ($user->isAdmin()) {
                return redirect()->route('dashboard.admin');
            } else {
                return redirect()->route('dashboard.user');
            }
        }

        // If not authenticated, redirect to login
        return redirect()->route('login');
    }
}