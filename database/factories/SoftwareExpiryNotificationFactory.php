<?php

namespace Database\Factories;

use App\Models\SoftwareExpiryNotification;
use App\Models\Software;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class SoftwareExpiryNotificationFactory extends Factory
{
    protected $model = SoftwareExpiryNotification::class;

    public function definition(): array
    {
        return [
            'software_id' => Software::inRandomOrder()->first()->id ?? Software::factory(),
            'notify_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'notification_id' => Notification::inRandomOrder()->first()->id ?? Notification::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

