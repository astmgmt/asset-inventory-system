<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetCondition;
use App\Models\AssetLocation;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    // Static counter to ensure unique asset codes in factory
    private static int $assetCodeCounter = 0;

    public function definition(): array
    {
        return [
            'asset_code' => $this->generateFactoryAssetCode(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'quantity' => 1,
            'serial_number' => strtoupper($this->faker->unique()->bothify('SN###??')),
            'model_number' => $this->faker->bothify('MODEL-###'),
            'category_id' => AssetCategory::inRandomOrder()->first()->id ?? AssetCategory::factory(),
            'condition_id' => AssetCondition::firstOrCreate(
                ['condition_name' => $this->faker->randomElement(['New', 'Available'])]
            )->id,
            'location_id' => AssetLocation::inRandomOrder()->first()->id ?? AssetLocation::factory(),
            'vendor_id' => Vendor::inRandomOrder()->first()->id,
            'warranty_expiration' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'is_disposed' => $this->faker->boolean(10),
            'expiry_flag' => $this->faker->boolean(10),
            'expiry_status' => $this->faker->randomElement([
                'warning_3m', 'warning_2m', 'warning_1m'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function generateFactoryAssetCode(): string
    {
        $date = now()->format('mdY'); // Format: 06162025
        $base = 10000000; // Offset base to prevent conflict with real asset codes
        $number = $base + self::$assetCodeCounter;
        self::$assetCodeCounter++;

        return "AST-{$date}-{$number}";
    }
}
