<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    public function definition(): array
    {
        return [
            'category_name' => fake()->unique()->randomElement([
                'Device', 'Equipment', 'Furniture', 'Supplies/Materials', 'Tools'
            ]),
        ];
    }
}
