<?php

namespace App\Traits;

trait AuthorizesAdmins
{
    public function isAuthorizedAdmin(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isSuperAdmin());
    }
}


