<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetCondition;
use App\Models\AssetLocation;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'asset_code' => strtoupper(fake()->unique()->bothify('ASSET-####')),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'quantity' => fake()->numberBetween(1, 100),
            'serial_number' => strtoupper(fake()->unique()->bothify('SN###??')),
            'model_number' => fake()->bothify('MODEL-###'),
            'category_id' => AssetCategory::inRandomOrder()->first()->id ?? AssetCategory::factory(),
            'condition_id' => AssetCondition::inRandomOrder()->first()->id ?? AssetCondition::factory(),
            'location_id' => AssetLocation::inRandomOrder()->first()->id ?? AssetLocation::factory(),
            'vendor_id' => Vendor::inRandomOrder()->first()->id,
            'warranty_expiration' => fake()->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'is_disposed' => fake()->boolean(20),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
