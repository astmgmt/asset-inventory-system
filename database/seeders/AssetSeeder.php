<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetCondition;
use App\Models\AssetLocation;
use App\Models\AssetDisposal;
use App\Models\Vendor;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // Seed 10 vendors
        Vendor::factory()->count(10)->create();

        // Seed fixed categories
        collect(['Device', 'Equipment', 'Furniture', 'Supplies/Materials', 'Tools'])
            ->each(fn($name) => \App\Models\AssetCategory::create(['category_name' => $name]));

        // Seed fixed conditions
        collect(['New', 'Borrowed', 'Available', 'Defective', 'Disposed'])
            ->each(fn($name) => \App\Models\AssetCondition::create(['condition_name' => $name]));

        // Seed fixed locations
        collect(['Building A', 'Building B', 'Building C', 'Building D'])
            ->each(fn($name) => \App\Models\AssetLocation::create(['location_name' => $name]));

        // Seed 20 assets
        Asset::factory()->count(20)->create();

        // Seed 20 disposals
        AssetDisposal::factory()->count(20)->create();
    }
}
