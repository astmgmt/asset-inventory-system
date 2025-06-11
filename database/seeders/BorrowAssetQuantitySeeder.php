<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\BorrowAssetQuantity; 

class BorrowAssetQuantitySeeder extends Seeder
{
    public function run(): void
    {
        $assets = Asset::all();

        foreach ($assets as $asset) {
            BorrowAssetQuantity::updateOrCreate(
                ['asset_id' => $asset->id],
                ['available_quantity' => $asset->quantity ?? 0]
            );
        }
    }
}
