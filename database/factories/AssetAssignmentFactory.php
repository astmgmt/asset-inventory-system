<?php

namespace Database\Factories;

use App\Models\AssetAssignment;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AssetAssignmentFactory extends Factory
{
    protected $model = AssetAssignment::class;

    public function definition(): array
    {
        return [
            'reference_no' => strtoupper('ASGN-' . Str::random(8)),
            'user_id' => User::factory(), // or specify a seeded user with 'User' role
            'admin_id' => User::factory(), // or specify a seeded admin
            'asset_id' => Asset::factory(),
            'purpose' => $this->faker->sentence,
            'remarks' => $this->faker->optional()->sentence,
            'date_assigned' => now(),
        ];
    }
}
