<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Software;
use App\Models\SoftwareAssignment;
use App\Models\SoftwareExpiryNotification;
use App\Models\User;

class SoftwareSeeder extends Seeder
{
    public function definition(): array
    {
        // Prevent factory usage in production
        if (app()->environment('production')) {
            return [];
        }
        
        // Original factory code below...
        return [ /* ... */ ];
    }
    public function run(): void
    {
        
    }
}
