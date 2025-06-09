<?php

namespace Database\Factories;

use App\Models\AssetLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetLocationFactory extends Factory
{
    protected $model = AssetLocation::class;

    public function definition(): array
    {
        return [
            'location_name' => fake()->unique()->randomElement([
                'Building A', 'Building B', 'Building C', 'Building D'
            ]),
        ];
    }
}