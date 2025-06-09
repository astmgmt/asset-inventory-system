<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetDisposalFactory extends Factory
{
    protected $model = AssetDisposal::class;

    public function definition(): array
    {
        return [
            'asset_id' => Asset::inRandomOrder()->first()->id,
            'disposed_by' => User::inRandomOrder()->first()->id,
            'disposal_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'reason' => fake()->sentence(),
        ];
    }
}
