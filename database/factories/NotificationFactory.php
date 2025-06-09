<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'type_id' => NotificationType::inRandomOrder()->first()->id ?? NotificationType::factory(),
            'message' => $this->faker->sentence(),
            'voice_alert' => $this->faker->boolean(),
            'email_alert' => $this->faker->boolean(),
            'sms_alert' => $this->faker->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

