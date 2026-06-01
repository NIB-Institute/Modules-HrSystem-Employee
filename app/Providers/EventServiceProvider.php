<?php

namespace Modules\Employee\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Telegram broadcasting on plan assignments runs DIRECTLY from the action
 * classes (CreateEmployeePlanAssignmentAction + BulkAssignEmployeePlanAction),
 * not via this event listener mapping. The direct call eliminates any chance
 * of Laravel's event/queue layer silently swallowing the broadcast.
 *
 * If we ever re-introduce queued or external listeners, wire them here.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];

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
