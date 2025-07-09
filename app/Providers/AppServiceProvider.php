<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// ADD THIS FOR NEW REGISTRATION
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Actions\Fortify\CreateNewUser;

//ADDED FOR CUSTOM REDIRECTION AFTER REGISTRATION
use Laravel\Fortify\Contracts\RegisterResponse;
use App\Actions\Fortify\RegisterRedirect;

//ADDED FOR CUSTOM DESIGN OF DASHBOARD
use Illuminate\Support\Facades\Blade;

// ADDED FOR FORGOT PASSWORD AND RESET PASSWORD
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use App\Actions\Fortify\ResetUserPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ADDED FOR CUSTOM REGISTRATION
        $this->app->bind(CreatesNewUsers::class, CreateNewUser::class);

        // ADDED FOR CUSTOM REDIRECTION AFTER REGISTRATION
        $this->app->bind(RegisterResponse::class, RegisterRedirect::class);

        //ADD FOR FORGOT PASSWORD AND RESET PASSWORD
        $this->app->bind(ResetsUserPasswords::class, ResetUserPassword::class);

    }

    
    public function boot(): void
    {
        //ADDED FOR CUSTOM LAYOUT DESIGN OF ROLE DASHBOARDS
        Blade::component('layouts.design_layout', 'design_layout');
    }
}
