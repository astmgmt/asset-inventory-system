<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Software;
use App\Models\SoftwareAssignment;
use App\Models\SoftwareExpiryNotification;
use App\Models\User;

class SoftwareSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() < 10) {
            \App\Models\User::factory()->count(10)->create();
        }

        Software::factory()->count(15)->create();

        SoftwareAssignment::factory()->count(25)->create();

        if (\App\Models\Notification::count() < 10) {
            \App\Models\Notification::factory()->count(10)->create();
        }

        SoftwareExpiryNotification::factory()->count(10)->create();
    }
}
