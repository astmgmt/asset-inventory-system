<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {

        $this->call([
            RoleSeeder::class,    
            UserSeeder::class,  
            DepartmentSeeder::class,  
            AssetSeeder::class,    
            AssetAssignmentSeeder::class,
            NotificationSeeder::class,
            SoftwareSeeder::class,
        ]);
        
    }
}
