<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $userRole = Role::where('name', 'User')->first();

        // Create a few departments first using factory
        $departments = Department::factory()->count(5)->create();

        User::create([
            'role_id' => $superAdminRole->id,
            'department_id' => $departments->random()->id,
            'name' => 'Super Administrator',
            'username' => 'super',
            'email' => 'ast.mgmt2025@gmail.com',
            'password' => Hash::make('Super1234'),
            'status' => 'Approved',
        ]);


        User::create([
            'role_id' => $superAdminRole->id,
            'department_id' => $departments->random()->id,
            'name' => 'Ryan Oliver Balboa',
            'username' => 'ryan',
            'email' => 'balboaryanoliver@gmail.com',
            'password' => Hash::make('Super1234'),
            'status' => 'Approved',
        ]);

        User::create([
            'role_id' => $adminRole->id,
            'department_id' => $departments->random()->id,
            'name' => 'John Leo Nacional',
            'username' => 'jomz',
            'email' => 'jomznacional@gmail.com',
            'password' => Hash::make('Admin1234'),
            'status' => 'Approved',
        ]);

        User::create([
            'role_id' => $userRole->id,
            'department_id' => $departments->random()->id,
            'name' => 'Kelvin Silva',
            'username' => 'kelvz',
            'email' => '23kelvinsilva@gmail.com',
            'password' => Hash::make('User1234'),
            'status' => 'Approved',
        ]);

        // Add 17 more users assigned to random departments
        User::factory()
            ->count(17)
            ->state(function () use ($userRole, $departments) {
                return [
                    'role_id' => $userRole->id,
                    'department_id' => $departments->random()->id,
                    'username' => fake()->unique()->userName(),
                    'status' => 'Approved',
                ];
            })
            ->create();
    }
}
