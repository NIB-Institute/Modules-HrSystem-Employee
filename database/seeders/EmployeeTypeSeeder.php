<?php

namespace Modules\Employee\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Employee\Models\EmployeeType;

class EmployeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Full Time',
                'description' => 'Full-time employees working standard hours with full benefits.',
                'time_start' => '08:00',
                'time_end' => '17:00',
            ],
            [
                'name' => 'Part Time',
                'description' => 'Part-time employees working reduced hours.',
                'time_start' => '09:00',
                'time_end' => '13:00',
            ],
            [
                'name' => 'Contract',
                'description' => 'Contract-based employees with fixed-term agreements.',
                'time_start' => '08:00',
                'time_end' => '17:00',
            ],
            [
                'name' => 'Intern',
                'description' => 'Interns or trainees in learning positions.',
                'time_start' => '09:00',
                'time_end' => '16:00',
            ],
            [
                'name' => 'Freelancer',
                'description' => 'Freelance workers on project-based assignments.',
                'time_start' => null,
                'time_end' => null,
            ],
            [
                'name' => 'Consultant',
                'description' => 'External consultants providing specialized expertise.',
                'time_start' => null,
                'time_end' => null,
            ],
        ];

        foreach ($types as $type) {
            EmployeeType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'uuid' => (string) Str::uuid(),
                    'description' => $type['description'],
                    'time_start' => $type['time_start'] ?? null,
                    'time_end' => $type['time_end'] ?? null,
                    'status' => true,
                ]
            );
        }

        $this->command->info('Created ' . count($types) . ' employee types.');
    }
}
