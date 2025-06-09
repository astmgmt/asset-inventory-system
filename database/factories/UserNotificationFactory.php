<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Notification;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'notification_id' => Notification::inRandomOrder()->first()->id ?? Notification::factory(),
            'is_read' => $this->faker->boolean(),
            'notified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
