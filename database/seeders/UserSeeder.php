<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $userRole = Role::where('name', 'User')->first();

        User::create([
            'role_id' => $superAdminRole->id,
            'name' => 'Ryan Oliver Balboa',
            'username' => 'ryan',
            'email' => 'ryan@test.com',
            'password' => Hash::make('Super1234'),
            'status' => 'Approved',
        ]);

        User::create([
            'role_id' => $adminRole->id,
            'name' => 'John Leo Nacional',
            'username' => 'jomz',
            'email' => 'jomz@test.com',
            'password' => Hash::make('Admin1234'),
            'status' => 'Approved',
        ]);

        User::create([
            'role_id' => $userRole->id,
            'name' => 'Kelvin Silva',
            'username' => 'kelvz',
            'email' => 'kelvz@test.com',
            'password' => Hash::make('User1234'),
            'status' => 'Approved',
        ]);

        // ADD 17 MORE USERS TO MAKE A TOTAL OF 20 USERS EXCLUDING THE THREE CREATED ABOVE
        User::factory()
            ->count(17)
            ->state(function () use ($userRole) {
                return [
                    'role_id' => $userRole->id,
                    'username' => fake()->unique()->userName(),
                    'status' => 'Approved',
                ];
            })
            ->create();
    }
}
