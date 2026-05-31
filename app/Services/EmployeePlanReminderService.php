<?php

namespace Modules\Employee\Services;

use Carbon\CarbonImmutable;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Enums\EmployeePlanReminderTierEnum;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanReminderLog;

/**
 * Finds (plan_id, occurrence_date, tier) triples whose fire-window contains $now,
 * and that don't already have a reminder_log row.
 *
 * One message per plan per occurrence (lists all active assignees).
 */
class EmployeePlanReminderService
{
    public function __construct(
        private readonly EmployeePlanOccurrenceCalculator $calculator,
    ) {
    }

    /**
     * @return array<int, array{plan_id:int, occurrence_date:string, tier:string}>
     */
    public function findDue(CarbonImmutable $now): array
    {
        $tiers = [
            EmployeePlanReminderTierEnum::GROUP_3D,
            EmployeePlanReminderTierEnum::GROUP_1D,
        ];

        // Lookahead window: longest tier is 3 days, plus 1 day slack.
        $lookaheadEnd = $now->addDays(4);

        // Only plans with a group chat ID configured AND at least one active assignee.
        $plans = EmployeePlan::query()
            ->whereNotNull('telegram_group_chat_id')
            ->whereHas('assignments', function ($q) {
                $q->whereIn('status', [
                    EmployeePlanAssignmentEnum::STATUS_ASSIGNED,
                    EmployeePlanAssignmentEnum::STATUS_IN_PROGRESS,
                ]);
            })
            ->get();

        $due = [];

        foreach ($plans as $plan) {
            $occurrences = $this->calculator->occurrencesBetween(
                $plan,
                $now->startOfDay(),
                $lookaheadEnd->endOfDay(),
            );

            foreach ($occurrences as $occurrence) {
                foreach ($tiers as $tier) {
                    if (! $this->isInFireWindow($now, $occurrence, $tier)) {
                        continue;
                    }

                    if ($this->alreadyLogged($plan->id, $occurrence->toDateString(), $tier->value)) {
                        continue;
                    }

                    $due[] = [
                        'plan_id' => $plan->id,
                        'occurrence_date' => $occurrence->toDateString(),
                        'tier' => $tier->value,
                    ];
                }
            }
        }

        return $due;
    }

    /**
     * Returns true if `$now` is inside the 1-hour fire window for this tier.
     */
    private function isInFireWindow(CarbonImmutable $now, CarbonImmutable $occurrence, EmployeePlanReminderTierEnum $tier): bool
    {
        $windowStart = $occurrence
            ->subDays($tier->daysBefore())
            ->setTime($tier->fireHour(), 0, 0);
        $windowEnd = $windowStart->addHour();

        return $now->greaterThanOrEqualTo($windowStart) && $now->lessThan($windowEnd);
    }

    private function alreadyLogged(int $planId, string $occurrenceDate, string $tier): bool
    {
        return EmployeePlanReminderLog::where('employee_plan_id', $planId)
            ->whereDate('occurrence_date', $occurrenceDate)
            ->where('tier', $tier)
            ->exists();
    }
}
