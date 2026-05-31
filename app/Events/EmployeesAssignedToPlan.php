<?php

namespace Modules\Employee\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired ONCE after a bulk assignment action, carrying all newly-created
 * assignment IDs. The listener posts a single consolidated Telegram message
 * to the plan's group listing every new assignee.
 */
class EmployeesAssignedToPlan
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $planId,
        /** @var array<int, int> */
        public readonly array $newAssignmentIds,
    ) {
    }
}
