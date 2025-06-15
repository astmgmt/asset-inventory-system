<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\Software;
use App\Services\SendEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendExpirationNotifications extends Command
{
    protected $signature = 'notifications:send-expiration';
    protected $description = 'Send expiration notifications for assets and software';

    public function handle()
    {
        $today = now();
        $startOfDay = $today->copy()->startOfDay();
        
        // Debug: Log current time
        Log::info("Expiration notification process started at: " . $today->toDateTimeString());
        
        // Reset any future last_notified_at values to null
        Asset::where('last_notified_at', '>', $today)
            ->update(['last_notified_at' => null]);
            
        Software::where('last_notified_at', '>', $today)
            ->update(['last_notified_at' => null]);

        // Get expiring assets that need notification
        $assets = Asset::where('expiry_flag', true)
            ->whereIn('expiry_status', ['warning_3m', 'warning_2m', 'warning_1m'])
            ->where(function($query) use ($startOfDay) {
                $query->whereNull('last_notified_at')
                      ->orWhere('last_notified_at', '<', $startOfDay);
            })
            ->get();

        // Get expiring software that need notification
        $software = Software::where('expiry_flag', true)
            ->whereIn('expiry_status', ['warning_3m', 'warning_2m', 'warning_1m'])
            ->where(function($query) use ($startOfDay) {
                $query->whereNull('last_notified_at')
                      ->orWhere('last_notified_at', '<', $startOfDay);
            })
            ->get();

        // Log counts
        Log::info("Assets to notify: " . $assets->count());
        Log::info("Software to notify: " . $software->count());

        // Get admin users
        $admins = User::whereHas('role', function($query) {
            $query->whereIn('name', ['Super Admin', 'Admin']);
        })->get();

        Log::info("Admins to notify: " . $admins->count());

        $emailService = new SendEmail();

        // Send asset notifications if needed
        if ($assets->count() > 0) {
            foreach ($admins as $admin) {
                $success = $emailService->send(
                    $admin->email,
                    'Asset Warranty Expiration Notification',
                    ['emails.asset-expiration', ['assets' => $assets]],
                    [], // CC
                    null, // attachment content
                    null, // attachment name
                    false // is HTML (using Blade view)
                );

                Log::info($success ? 
                    "Asset email sent to {$admin->email}" : 
                    "Failed to send asset email to {$admin->email}"
                );
            }

            // Update last notified at
            Asset::whereIn('id', $assets->pluck('id'))
                ->update(['last_notified_at' => $today]);
        }

        // Send software notifications if needed
        if ($software->count() > 0) {
            foreach ($admins as $admin) {
                $success = $emailService->send(
                    $admin->email,
                    'Software Subscription Expiration Notification',
                    ['emails.software-expiration', ['software' => $software]],
                    [], // CC
                    null, // attachment content
                    null, // attachment name
                    false // is HTML (using Blade view)
                );

                Log::info($success ? 
                    "Software email sent to {$admin->email}" : 
                    "Failed to send software email to {$admin->email}"
                );
            }

            // Update last notified at
            Software::whereIn('id', $software->pluck('id'))
                ->update(['last_notified_at' => $today]);
        }

        $this->info('Expiration notifications processed. ' . 
                   'Assets: ' . $assets->count() . 
                   ', Software: ' . $software->count());
    }
}