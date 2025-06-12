<?php

namespace Database\Factories;

use App\Models\AssetReturnItem;
use App\Models\AssetBorrowItem;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AssetReturnItemFactory extends Factory
{
    protected $model = AssetReturnItem::class;

    public function definition(): array
    {
        $borrowItem = AssetBorrowItem::inRandomOrder()->first();

        return [
            'return_code' => 'RT-' . now()->format('Ymd') . '-' . str_pad($this->faker->unique()->numberBetween(1, 99999999), 8, '0', STR_PAD_LEFT),
            'borrow_item_id' => $borrowItem->id,
            'returned_by_user_id' => $borrowItem->borrowTransaction->user_id,
            'returned_by_department_id' => $borrowItem->borrowTransaction->user_department_id,
            'returned_at' => now(),
            'remarks' => $this->faker->sentence(),
        ];
    }
}
