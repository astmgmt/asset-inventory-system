<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\RegisterResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterRedirect implements RegisterResponse
{
    public function toResponse($request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role->name === 'Super Admin') {
            return redirect()->route('dashboard.superadmin');
        } elseif ($user->role->name === 'Admin') {
            return redirect()->route('dashboard.admin');
        } else {
            return redirect()->route('dashboard.user');
        }
    }
}
