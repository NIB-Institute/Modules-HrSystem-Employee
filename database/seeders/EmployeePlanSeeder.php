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

        // Mix of English + Khmer (ភាសាខ្មែរ) titles so the seeded data exercises both locales.
        $titles = [
            // English
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
            // Khmer
            'សិក្ខាសាលាបណ្តុះបណ្តាលនិយោជិតថ្មី ត្រីមាសទី១',
            'កម្មវិធីពង្រឹងសមត្ថភាពលក់',
            'វគ្គបណ្តុះបណ្តាលអនុលោមភាពប្រចាំឆ្នាំ',
            'វគ្គអភិវឌ្ឍន៍ភាពជាអ្នកដឹកនាំ',
            'ទិវាសហប្រតិបត្តិការក្រុមឆ្លងផ្នែក',
            'ការអនុវត្តន៍សុខភាព និងសុវត្ថិភាព',
            'ដំណើរកម្សាន្តក្រុមហ៊ុនប្រចាំឆ្នាំ',
            'ការបន្តបណ្តុះបណ្តាលសេវាអតិថិជន',
            'ការយល់ដឹងសន្តិសុខអ៊ីនធឺណិត ឆ្នាំ២០២៦',
            'វដ្តពិនិត្យការអនុវត្តន៍ការងារ',
        ];

        $descriptionTemplates = [
            // English
            'Structured program covering key milestones, mentor pairing, and a final review.',
            'Hands-on workshop with daily check-ins and end-of-week deliverables.',
            'Self-paced learning track with weekly progress check-ins.',
            'Cross-functional initiative requiring participation from multiple teams.',
            // Khmer
            'កម្មវិធីដែលមានរបៀបវារៈគ្របដណ្តប់លើ កិច្ចសម្គាល់សំខាន់ៗ ការផ្គូផ្គងជាមួយអ្នកណែនាំ និងការត្រួតពិនិត្យចុងក្រោយ។',
            'សិក្ខាសាលាជាក់ស្តែង ជាមួយការត្រួតពិនិត្យប្រចាំថ្ងៃ និងលទ្ធផលនៅចុងសប្តាហ៍។',
            'កម្មវិធីសិក្សាដោយខ្លួនឯង ជាមួយការត្រួតពិនិត្យវឌ្ឍនភាពប្រចាំសប្តាហ៍។',
            'គំនិតផ្តួចផ្តើមឆ្លងផ្នែក ដែលត្រូវការការចូលរួមពីក្រុមច្រើន។',
            null,
        ];

        $locations = [
            // English
            'Head Office - Conference Room A',
            'Head Office - Training Center',
            'Branch Office - Phnom Penh',
            'Online (Zoom)',
            'Hybrid (Office + Remote)',
            // Khmer
            'ការិយាល័យកណ្តាល - បន្ទប់ប្រជុំ A',
            'ការិយាល័យកណ្តាល - មជ្ឈមណ្ឌលបណ្តុះបណ្តាល',
            'ការិយាល័យសាខា - ភ្នំពេញ',
            'អនឡាញ (Zoom)',
            'បន្សំ (ការិយាល័យ + ពីចម្ងាយ)',
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
