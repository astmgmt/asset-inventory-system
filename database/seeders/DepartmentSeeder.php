<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'IT Department', 'description' => 'Handles technology infrastructure'],
            ['name' => 'HR Department', 'description' => 'Manages human resources'],
            ['name' => 'Finance Department', 'description' => 'Handles financial operations'],
            ['name' => 'Logistics Department', 'description' => 'Manages supply chain']
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['name' => $dept['name']],
                $dept
            );
        }
    }
}
