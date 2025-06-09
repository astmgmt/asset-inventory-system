<?php

namespace Database\Factories;

use App\Models\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationTypeFactory extends Factory
{
    protected $model = NotificationType::class;

    public function definition(): array
    {
        return [
            'type_name' => $this->faker->unique()->randomElement(['sms', 'voice', 'email']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
