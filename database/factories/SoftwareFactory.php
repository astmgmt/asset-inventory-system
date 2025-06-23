<?php

namespace Database\Factories;

use App\Models\Software;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SoftwareFactory extends Factory
{
    protected $model = Software::class;

    public function definition(): array
    {
        return [
            'software_code' => strtoupper(fake()->unique()->bothify('SW-###??')),
            'software_name' => fake()->word() . ' Suite',
            'description' => fake()->optional()->sentence(),
            'license_key' => strtoupper(fake()->bothify('KEY-####-####-####')),
            'expiry_date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'added_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'expiry_flag' => fake()->boolean(10),       
            'expiry_status' => fake()->randomElement([
                'warning_3m', 'warning_2m', 'warning_1m'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
