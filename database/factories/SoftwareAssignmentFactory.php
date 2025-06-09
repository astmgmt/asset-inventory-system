<?php

namespace Database\Factories;

use App\Models\SoftwareAssignment;
use App\Models\Software;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SoftwareAssignmentFactory extends Factory
{
    protected $model = SoftwareAssignment::class;

    public function definition(): array
    {
        return [
            'software_id' => Software::inRandomOrder()->first()->id ?? Software::factory(),
            'assigned_to_user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'assigned_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
