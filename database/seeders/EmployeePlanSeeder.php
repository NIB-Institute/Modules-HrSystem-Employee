<?php

namespace Modules\Employee\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Employee\Enums\EmployeePlanEnum;
use Modules\Employee\Models\EmployeePlan;

class EmployeePlanSeeder extends Seeder
{
    public function run(): void
    {
        $createdBy = User::query()->first()?->id;

        $titles = [
            'Q1 Onboarding Workshop',
            'Sales Enablement Program',
            'Annual Compliance Training',
            'Leadership Development Track',
            'Cross-Team Collaboration Day',
            'Health & Safety Drill',
            'Annual Company Retreat',
            'Customer Service Refresher',
            'Cybersecurity Awareness 2026',
            'Performance Review Cycle',
        ];

        $descriptionTemplates = [
            'Structured program covering key milestones, mentor pairing, and a final review.',
            'Hands-on workshop with daily check-ins and end-of-week deliverables.',
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

        $validityOptions = [null, 12, 24, 36];

        $this->command->info('Creating employee plans...');

        $count = 0;
        foreach ($titles as $title) {
            $startDate = now()->addDays(rand(-30, 30))->startOfDay();
            $endDate = (clone $startDate)->addDays(rand(0, 14));

            $hasTimes = (bool) rand(0, 1);
            $startTime = $hasTimes ? sprintf('%02d:%02d', rand(7, 12), [0, 15, 30, 45][rand(0, 3)]) : null;
            $endTime = $hasTimes ? sprintf('%02d:%02d', rand(13, 18), [0, 15, 30, 45][rand(0, 3)]) : null;

            $scheduleMode = $scheduleModes[array_rand($scheduleModes)];
            $isRecurring = $scheduleMode === EmployeePlanEnum::SCHEDULE_MODE_RECURRING;
            $recurrenceType = $isRecurring ? $recurrenceTypes[array_rand($recurrenceTypes)] : null;

            EmployeePlan::create([
                'title' => $title,
                'description' => $descriptionTemplates[array_rand($descriptionTemplates)],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'priority' => $priorities[array_rand($priorities)],
                'location' => $locations[array_rand($locations)],
                'schedule_mode' => $scheduleMode,
                'is_recurring' => $isRecurring,
                'recurrence_type' => $recurrenceType,
                'status' => $statuses[array_rand($statuses)],
                'valid_for_months' => $validityOptions[array_rand($validityOptions)],
                'created_by' => $createdBy,
            ]);

            $count++;
        }

        $this->command->info("Created {$count} plans successfully!");

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
