<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationType;
use App\Models\Notification;
use App\Models\UserNotification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        collect(['sms', 'voice', 'email'])
            ->each(fn($type) => NotificationType::firstOrCreate(['type_name' => $type]));

        Notification::factory()->count(20)->create();

        if (User::count() === 0) {
            \App\Models\User::factory()->count(5)->create();
        }

        UserNotification::factory()->count(20)->create();
    }
}
