<?php

namespace Modules\Employee\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employee\Enums\EmployeePlanEnum;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeePlan;

class EmployeePlanSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('status', true)->take(10)->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Please seed employees first.');
            return;
        }

        $titleTemplates = [
            'Onboarding Plan for :name',
            'Q1 Training Roadmap',
            'Sales Enablement Workshop',
            'Performance Review Cycle',
            'Quarterly OKR Planning',
            'Skills Development Sprint',
            'Cross-Team Collaboration Day',
            'Health & Safety Drill',
            'Annual Company Retreat',
            'Compliance Training Module',
        ];

        $descriptionTemplates = [
            'Structured plan covering key milestones, daily check-ins, and a final review.',
            'Hands-on workshop with mentor pairing and end-of-week deliverables.',
            'Self-paced learning track with weekly progress check-ins.',
            'Cross-functional initiative requiring participation from multiple teams.',
            null,
        ];

        $locations = [
            'Head Office - Conference Room A',
            'Head Office - Training Center',
            'Branch Office - Phnom Penh',
            'Online (Zoom)',
            'Hybrid (Office + Remote)',
            null,
        ];

        $priorities = EmployeePlanEnum::PRIORITIES;
        $scheduleModes = EmployeePlanEnum::SCHEDULE_MODES;
        $recurrenceTypes = EmployeePlanEnum::RECURRENCE_TYPES;
        $statuses = EmployeePlanEnum::STATUSES;

        $employeeIds = $employees->pluck('id')->all();

        $this->command->info('Creating employee plans...');

        $count = 0;
        foreach ($employees as $employee) {
            $numPlans = rand(2, 4);

            for ($i = 0; $i < $numPlans; $i++) {
                $title = str_replace(
                    ':name',
                    trim("{$employee->first_name} {$employee->last_name}") ?: $employee->employee_code,
                    $titleTemplates[array_rand($titleTemplates)],
                );

                $startDate = now()->addDays(rand(-30, 30))->startOfDay();
                $endDate = (clone $startDate)->addDays(rand(0, 14));

                $hasTimes = (bool) rand(0, 1);
                $startTime = $hasTimes ? sprintf('%02d:%02d', rand(7, 12), [0, 15, 30, 45][rand(0, 3)]) : null;
                $endTime = $hasTimes ? sprintf('%02d:%02d', rand(13, 18), [0, 15, 30, 45][rand(0, 3)]) : null;

                $scheduleMode = $scheduleModes[array_rand($scheduleModes)];
                $isRecurring = $scheduleMode === EmployeePlanEnum::SCHEDULE_MODE_RECURRING;
                $recurrenceType = $isRecurring
                    ? $recurrenceTypes[array_rand($recurrenceTypes)]
                    : null;

                $participantCount = rand(0, 3);
                $participants = $participantCount === 0
                    ? null
                    : collect($employeeIds)
                        ->reject(fn ($id) => $id === $employee->id)
                        ->shuffle()
                        ->take($participantCount)
                        ->values()
                        ->all();

                EmployeePlan::create([
                    'employee_id' => $employee->id,
                    'employee_availability_id' => null,
                    'title' => $title,
                    'description' => $descriptionTemplates[array_rand($descriptionTemplates)],
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'priority' => $priorities[array_rand($priorities)],
                    'location' => $locations[array_rand($locations)],
                    'schedule_mode' => $scheduleMode,
                    'participants' => $participants,
                    'is_recurring' => $isRecurring,
                    'recurrence_type' => $recurrenceType,
                    'status' => $statuses[array_rand($statuses)],
                ]);

                $count++;
            }
        }

        $this->command->info("Created {$count} employee plans successfully!");

        $stats = [
            'Total' => EmployeePlan::count(),
            'Scheduled' => EmployeePlan::where('status', EmployeePlanEnum::STATUS_SCHEDULED)->count(),
            'In Progress' => EmployeePlan::where('status', EmployeePlanEnum::STATUS_IN_PROGRESS)->count(),
            'Completed' => EmployeePlan::where('status', EmployeePlanEnum::STATUS_COMPLETED)->count(),
            'Cancelled' => EmployeePlan::where('status', EmployeePlanEnum::STATUS_CANCELLED)->count(),
        ];

        $this->command->table(
            ['Status', 'Count'],
            collect($stats)->map(fn ($v, $k) => [$k, $v])->toArray(),
        );
    }
}
