<?php

namespace Database\Factories;

use App\Models\AssetCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetConditionFactory extends Factory
{
    protected $model = AssetCondition::class;

    public function definition(): array
    {
        return [
            'condition_name' => fake()->unique()->randomElement([
                'New', 'Available', 'Defective', 'Borrowed', 'Disposed', 'Returned'
            ]),
        ];
    }
}
