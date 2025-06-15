<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;


class Kernel extends ConsoleKernel
{
    
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('notifications:send-expiration')
            ->dailyAt('08:00')
            ->onSuccess(function () {
                \Log::info('Expiration notifications command ran successfully');
            })
            ->onFailure(function () {
                \Log::error('Expiration notifications command failed');
            });
    }

   
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
