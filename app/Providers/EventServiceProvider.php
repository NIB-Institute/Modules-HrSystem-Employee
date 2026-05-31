<?php

namespace Modules\Employee\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Employee\Events\EmployeePlanAssignmentCreated;
use Modules\Employee\Events\EmployeesAssignedToPlan;
use Modules\Employee\Listeners\SendBatchAssignmentNotificationListener;
use Modules\Employee\Listeners\SendOnAssignmentReminderListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // Single-assign endpoint (POST /employee-plan-assignments).
        EmployeePlanAssignmentCreated::class => [
            SendOnAssignmentReminderListener::class,
        ],
        // Bulk-assign endpoint (POST /employee-plan-assignments/bulk-assign).
        // Listener posts ONE message listing all new assignees.
        EmployeesAssignedToPlan::class => [
            SendBatchAssignmentNotificationListener::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
