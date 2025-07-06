<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,    
            DepartmentSeeder::class, 
            UserSeeder::class,  
            AssetSeeder::class,
        ]);
        
        $notificationTypes = [
            'email_notification' => 'Regular email notifications for all admins',
            'super_admin_email_notification' => 'Special notifications only for Super Admins',
            'borrow_request' => 'Asset borrow requests',
            'return_request' => 'Asset return requests',
            'user_approval' => 'Pending user approvals'
        ];
        
        foreach ($notificationTypes as $type => $description) {
            NotificationType::firstOrCreate([
                'type_name' => $type
            ], [
                'type_name' => $type,
            ]);
        }
    }
}