<?php

namespace Modules\Employee\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Employee\Models\EmployeeType;

class EmployeeTypeFactory extends Factory
{
    protected $model = EmployeeType::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->unique()->randomElement([
                'Full Time',
                'Part Time',
                'Contract',
                'Intern',
                'Freelancer',
                'Consultant',
            ]),
            'description' => fake()->sentence(),
            'status' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
