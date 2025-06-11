<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = ['IT Department', 'HR Department', 'Finance Department', 'Logistics Department'];

        foreach ($departments as $dept) {
            Department::create([
                'name' => $dept,
                'description' => $dept . ' handles all related tasks.'
            ]);
        }
    }
}
