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
        $currentUrl = $request->fullUrl(); 
        $borrowRequestsUrl = 'http://asset-management.test/borrow/requests';

        if ($user && $user->role && in_array($user->role->name, $roles)) {
            return $next($request); 
        }

        // Custom logic based on role and target URL
        if ($user) {
            if ($user->isSuperAdmin()) {
                if ($currentUrl === $borrowRequestsUrl) {
                    return redirect('dashboard/superadmin'); 
                } else {
                    return redirect()->route('dashboard.superadmin');
                }
            } elseif ($user->isAdmin()) {
                if ($currentUrl === $borrowRequestsUrl) {
                    return redirect('dashboard/admin'); 
                } else {
                    return redirect()->route('dashboard.admin');
                }
            } else { // User role
                if ($currentUrl === $borrowRequestsUrl) {
                    Auth::guard('web')->logout();
                    return redirect()->route('login')->withErrors(['Access denied to this resource. You must be an administrator!']);
                } else {
                    return redirect()->route('dashboard.user');
                }
            }
        }

        // If not authenticated
        return redirect()->route('login');
    }
}
