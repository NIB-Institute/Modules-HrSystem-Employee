<?php

namespace Modules\Employee\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\PermissionRequest;

class PermissionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a reviewer (first available user)
        $reviewer = User::first();
        if (!$reviewer) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // Get active employees
        $employees = Employee::where('status', true)->take(10)->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Please seed employees first.');
            return;
        }

        $types = [
            PermissionRequest::TYPE_LEAVE,
            PermissionRequest::TYPE_OVERTIME,
            PermissionRequest::TYPE_REMOTE,
            PermissionRequest::TYPE_EARLY_LEAVE,
            PermissionRequest::TYPE_LATE_ARRIVAL,
            PermissionRequest::TYPE_OTHER,
        ];

        $reasons = [
            PermissionRequest::TYPE_LEAVE => [
                'Family emergency - need to attend to a sick family member',
                'Annual vacation planned for family trip',
                'Personal health check-up appointment scheduled',
                'Wedding ceremony of close relative',
                'Moving to a new residence',
            ],
            PermissionRequest::TYPE_OVERTIME => [
                'Need to complete urgent project deliverable before deadline',
                'Client presentation preparation required',
                'System maintenance scheduled for off-hours',
                'Month-end reporting and reconciliation',
                'Critical bug fix deployment needed',
            ],
            PermissionRequest::TYPE_REMOTE => [
                'Home renovation work being done, need to supervise',
                'Child care arrangement issues this week',
                'Internet connectivity at home is excellent for focused work',
                'Avoiding long commute during road construction',
                'Weather conditions making travel difficult',
            ],
            PermissionRequest::TYPE_EARLY_LEAVE => [
                'Doctor appointment scheduled for afternoon',
                'Need to pick up child from school early',
                'Bank appointment that cannot be rescheduled',
                'Home delivery of important documents',
                'Vehicle service appointment',
            ],
            PermissionRequest::TYPE_LATE_ARRIVAL => [
                'Morning medical appointment scheduled',
                'Traffic due to road accident on usual route',
                'Public transport delays expected',
                'Early morning house maintenance visit',
                'Dropping child to school on first day',
            ],
            PermissionRequest::TYPE_OTHER => [
                'Need to attend government office for document renewal',
                'Jury duty summons received',
                'Attending professional certification exam',
                'Participating in community volunteer event',
                'Religious observance day',
            ],
        ];

        $statuses = [
            PermissionRequest::STATUS_PENDING,
            PermissionRequest::STATUS_APPROVED,
            PermissionRequest::STATUS_REJECTED,
        ];

        $reviewNotes = [
            'approved' => [
                'Approved as requested. Please ensure proper handover.',
                'Request approved. Enjoy your time off!',
                'Approved. Please coordinate with team lead.',
                'Request granted. Keep us updated.',
                null,
            ],
            'rejected' => [
                'Unfortunately, we cannot approve due to upcoming deadline.',
                'Rejected due to insufficient notice period.',
                'Cannot approve at this time due to team shortage.',
                'Please reschedule to a different date.',
                'Overlaps with critical project phase.',
            ],
        ];

        $rejectedReasons = [
            'Critical project deadline requires full team presence',
            'Multiple team members already on leave during this period',
            'Insufficient documentation provided',
            'Request conflicts with mandatory training session',
            'Department policy requires 2-week advance notice',
        ];

        $this->command->info('Creating permission requests...');

        $count = 0;
        foreach ($employees as $employee) {
            // Create 2-5 requests per employee
            $numRequests = rand(2, 5);

            for ($i = 0; $i < $numRequests; $i++) {
                $type = $types[array_rand($types)];
                $reason = $reasons[$type][array_rand($reasons[$type])];
                $status = $statuses[array_rand($statuses)];

                // Generate date range
                $fromDate = now()->subDays(rand(-30, 60))->startOfDay();
                $toDate = (clone $fromDate)->addDays(rand(1, 5));

                // For overtime, dates are usually same day
                if ($type === PermissionRequest::TYPE_OVERTIME) {
                    $toDate = clone $fromDate;
                }

                // For early leave and late arrival, usually same day
                if (in_array($type, [PermissionRequest::TYPE_EARLY_LEAVE, PermissionRequest::TYPE_LATE_ARRIVAL])) {
                    $toDate = clone $fromDate;
                }

                $data = [
                    'employee_id' => $employee->id,
                    'type' => $type,
                    'reason' => $reason,
                    'from_date' => $fromDate->format('Y-m-d'),
                    'to_date' => $toDate->format('Y-m-d'),
                    'request_date' => $fromDate->subDays(rand(3, 14)),
                    'status' => $status,
                    'rejected_status' => false,
                    'rejected_reason' => null,
                ];

                // Add review data for non-pending requests
                if ($status !== PermissionRequest::STATUS_PENDING) {
                    $data['reviewed_by'] = $reviewer->id;
                    $data['reviewed_at'] = $data['request_date']->addDays(rand(1, 3));

                    if ($status === PermissionRequest::STATUS_APPROVED) {
                        $data['review_note'] = $reviewNotes['approved'][array_rand($reviewNotes['approved'])];
                    } else {
                        $data['review_note'] = $reviewNotes['rejected'][array_rand($reviewNotes['rejected'])];
                        // Some rejected requests have rejected_status and rejected_reason
                        if (rand(0, 1)) {
                            $data['rejected_status'] = true;
                            $data['rejected_reason'] = $rejectedReasons[array_rand($rejectedReasons)];
                        }
                    }
                }

                PermissionRequest::create($data);
                $count++;
            }
        }

        $this->command->info("Created {$count} permission requests successfully!");

        // Display stats
        $stats = [
            'Total' => PermissionRequest::count(),
            'Pending' => PermissionRequest::pending()->count(),
            'Approved' => PermissionRequest::approved()->count(),
            'Rejected' => PermissionRequest::rejected()->count(),
        ];

        $this->command->table(['Status', 'Count'], collect($stats)->map(fn ($v, $k) => [$k, $v])->toArray());
    }
}
