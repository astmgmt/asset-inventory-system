<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\AssetAssignment;
use App\Models\User;
use App\Models\Asset;
use App\Models\Role;

class AssetAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get role IDs
        $userRoleId = Role::where('name', 'User')->value('id');
        $adminRoleIds = Role::whereIn('name', ['Admin', 'Super Admin'])->pluck('id');

        // Get user IDs by role
        $users = User::where('role_id', $userRoleId)->pluck('id');
        $admins = User::whereIn('role_id', $adminRoleIds)->pluck('id');

        $assets = Asset::pluck('id');

        foreach (range(1, 20) as $i) {
            AssetAssignment::create([
                'reference_no' => 'ASSIGN-' . strtoupper(Str::random(8)),
                'user_id' => $users->random(),
                'admin_id' => $admins->random(),
                'asset_id' => $assets->random(),
                'purpose' => fake()->sentence,
                'remarks' => fake()->optional()->sentence,
                'date_assigned' => now(),
            ]);
        }
    }
}
