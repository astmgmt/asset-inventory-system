<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        if (app()->environment('production')) {
            throw new \Exception('Factory usage is disabled in production environment');
        }
        
        return [
            'name' => fake()->unique()->company(),
            'description' => fake()->sentence(),
        ];
    }
}
