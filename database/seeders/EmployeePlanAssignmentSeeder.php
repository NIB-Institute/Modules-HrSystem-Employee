<?php

namespace Modules\Employee\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeAvailability;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanAssignment;

class EmployeePlanAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $plans = EmployeePlan::all();
        $employees = Employee::where('status', true)->get();

        if ($plans->isEmpty() || $employees->isEmpty()) {
            $this->command->warn('Need plans and employees first. Skipping.');
            return;
        }

        $statuses = EmployeePlanAssignmentEnum::STATUSES;

        $this->command->info('Creating plan assignments...');

        $count = 0;
        foreach ($plans as $plan) {
            $assignees = $employees->random(min(rand(3, 8), $employees->count()));

            foreach ($assignees as $employee) {
                $status = $statuses[array_rand($statuses)];

                $availability = EmployeeAvailability::query()
                    ->where('employee_id', $employee->id)
                    ->where('is_active', true)
                    ->inRandomOrder()
                    ->first();

                $data = [
                    'employee_plan_id' => $plan->id,
                    'employee_id' => $employee->id,
                    'employee_availability_id' => rand(0, 1) ? $availability?->id : null,
                    'status' => $status,
                    'assigned_at' => now()->subDays(rand(1, 60)),
                    'notes' => rand(0, 2) === 0 ? 'Auto-seeded assignment' : null,
                ];

                if ($status === EmployeePlanAssignmentEnum::STATUS_IN_PROGRESS) {
                    $data['started_at'] = now()->subDays(rand(1, 30));
                }

                if ($status === EmployeePlanAssignmentEnum::STATUS_COMPLETED) {
                    $data['started_at'] = now()->subDays(rand(15, 60));
                    $data['completed_at'] = now()->subDays(rand(1, 14));
                    if ($plan->valid_for_months) {
                        $data['expires_at'] = $data['completed_at']->copy()->addMonths($plan->valid_for_months);
                    }
                }

                if ($status === EmployeePlanAssignmentEnum::STATUS_EXPIRED) {
                    $data['started_at'] = now()->subYears(2);
                    $data['completed_at'] = now()->subYears(2)->addMonths(2);
                    $data['expires_at'] = now()->subDays(rand(1, 30));
                }

                EmployeePlanAssignment::create($data);
                $count++;
            }
        }

        $this->command->info("Created {$count} plan assignments successfully!");

        $stats = [
            'Total' => EmployeePlanAssignment::count(),
            'Assigned' => EmployeePlanAssignment::where('status', EmployeePlanAssignmentEnum::STATUS_ASSIGNED)->count(),
            'In Progress' => EmployeePlanAssignment::where('status', EmployeePlanAssignmentEnum::STATUS_IN_PROGRESS)->count(),
            'Completed' => EmployeePlanAssignment::where('status', EmployeePlanAssignmentEnum::STATUS_COMPLETED)->count(),
            'Dropped' => EmployeePlanAssignment::where('status', EmployeePlanAssignmentEnum::STATUS_DROPPED)->count(),
            'No Show' => EmployeePlanAssignment::where('status', EmployeePlanAssignmentEnum::STATUS_NO_SHOW)->count(),
            'Expired' => EmployeePlanAssignment::where('status', EmployeePlanAssignmentEnum::STATUS_EXPIRED)->count(),
        ];

        $this->command->table(
            ['Status', 'Count'],
            collect($stats)->map(fn ($v, $k) => [$k, $v])->toArray(),
        );
    }
}
