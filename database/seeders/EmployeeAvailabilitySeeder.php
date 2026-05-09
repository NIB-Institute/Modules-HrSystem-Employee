<?php

namespace Modules\Employee\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employee\Enums\EmployeeAvailabilityEnum;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeAvailability;

class EmployeeAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('status', true)->take(10)->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Please seed employees first.');
            return;
        }

        $shiftPresets = [
            'standard' => ['start' => '09:00', 'end' => '17:00', 'note' => null],
            'morning' => ['start' => '07:00', 'end' => '12:00', 'note' => 'Morning shift'],
            'afternoon' => ['start' => '13:00', 'end' => '18:00', 'note' => 'Afternoon shift'],
            'evening' => ['start' => '15:00', 'end' => '22:00', 'note' => 'Evening shift'],
        ];

        $this->command->info('Creating employee availability slots...');

        $count = 0;
        foreach ($employees as $employee) {
            $preset = $shiftPresets[array_rand($shiftPresets)];

            // Standard weekday coverage Mon-Fri
            foreach (EmployeeAvailabilityEnum::WEEKDAYS as $day) {
                EmployeeAvailability::create([
                    'employee_id' => $employee->id,
                    'day_of_week' => $day,
                    'start_time' => $preset['start'],
                    'end_time' => $preset['end'],
                    'is_active' => true,
                    'notes' => $preset['note'],
                ]);
                $count++;
            }

            // ~50% of employees also work Saturday morning
            if (rand(0, 1) === 1) {
                EmployeeAvailability::create([
                    'employee_id' => $employee->id,
                    'day_of_week' => EmployeeAvailabilityEnum::DAY_SATURDAY,
                    'start_time' => '09:00',
                    'end_time' => '13:00',
                    'is_active' => true,
                    'notes' => 'Half-day Saturday',
                ]);
                $count++;
            }

            // ~25% of employees have an inactive Sunday slot from a previous schedule
            if (rand(1, 4) === 1) {
                EmployeeAvailability::create([
                    'employee_id' => $employee->id,
                    'day_of_week' => EmployeeAvailabilityEnum::DAY_SUNDAY,
                    'start_time' => '10:00',
                    'end_time' => '14:00',
                    'is_active' => false,
                    'notes' => 'Previous schedule, kept for reference',
                ]);
                $count++;
            }
        }

        $this->command->info("Created {$count} availability slots successfully!");

        $stats = [
            'Total' => EmployeeAvailability::count(),
            'Active' => EmployeeAvailability::where('is_active', true)->count(),
            'Inactive' => EmployeeAvailability::where('is_active', false)->count(),
            'Employees Covered' => EmployeeAvailability::distinct('employee_id')->count('employee_id'),
        ];

        $this->command->table(
            ['Metric', 'Count'],
            collect($stats)->map(fn ($v, $k) => [$k, $v])->toArray(),
        );
    }
}
