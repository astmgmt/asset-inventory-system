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
            'software_id'   => Software::inRandomOrder()->first()->id ?? Software::factory(),
            'user_id'       => User::inRandomOrder()->first()->id ?? User::factory(),
            'admin_id'      => User::inRandomOrder()->first()->id ?? User::factory(), // Optional: restrict to Admin role if needed
            'reference_no'  => strtoupper(fake()->bothify('REF-###-??')),
            'purpose'       => fake()->sentence(3),
            'remarks'       => fake()->optional()->sentence(),
            'date_assigned' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
