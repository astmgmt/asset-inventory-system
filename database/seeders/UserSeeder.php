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
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $userRole = Role::firstOrCreate(['name' => 'User']);
        
        $itDept = Department::where('name', 'IT Department')->first();
        
        if (!$itDept) {
            throw new \Exception('IT Department not found. Run DepartmentSeeder first.');
        }

        $users = [
            [
                'role_id' => $superAdminRole->id,
                'department_id' => $itDept->id,
                'name' => 'Super Administrator',
                'username' => 'super',
                'email' => 'ast.mgmt2025@gmail.com',
                'password' => Hash::make('Super1234'),
                'status' => 'Approved',
            ],
            [
                'role_id' => $superAdminRole->id,
                'department_id' => $itDept->id,
                'name' => 'Ryan Oliver Balboa',
                'username' => 'ryan',
                'email' => 'balboaryanoliver@gmail.com',
                'password' => Hash::make('Super1234'),
                'status' => 'Approved',
            ],
            [
                'role_id' => $adminRole->id,
                'department_id' => $itDept->id,
                'name' => 'John Leo Nacional',
                'username' => 'jomz',
                'email' => 'jomznacional@gmail.com',
                'password' => Hash::make('Admin1234'),
                'status' => 'Approved',
            ],
            [
                'role_id' => $userRole->id,
                'department_id' => $itDept->id,
                'name' => 'Kelvin Silva',
                'username' => 'kelvz',
                'email' => '23kelvinsilva@gmail.com',
                'password' => Hash::make('User1234'),
                'status' => 'Approved',
            ]
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
