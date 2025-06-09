<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'vendor_name'     => $this->faker->company,
            'contact_person'  => $this->faker->name,
            'phone'           => $this->faker->phoneNumber,
            'email'           => $this->faker->unique()->safeEmail,
            'address'         => $this->faker->address,
        ];
    }
}
