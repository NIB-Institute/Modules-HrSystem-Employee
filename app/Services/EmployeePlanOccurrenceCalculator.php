<?php

namespace Modules\Employee\Services;

use Carbon\CarbonImmutable;
use Modules\Employee\Models\EmployeePlan;

/**
 * Pure helper: compute the dates on which a plan "occurs" between two bounds.
 *
 * For non-recurring plans, the only occurrence is `plan.start_date` (if it lies in [from, to]).
 * For recurring plans, walks from max(plan.start_date, from) to min(plan.end_date, to)
 * stepping by recurrence_type.
 *
 * Hard-caps the lookahead at 365 days from `from` so a daily recurrence with a 5-year
 * end_date can't OOM the result set.
 */
class EmployeePlanOccurrenceCalculator
{
    private const MAX_LOOKAHEAD_DAYS = 365;

    /**
     * @return array<int, CarbonImmutable>
     */
    public function occurrencesBetween(EmployeePlan $plan, CarbonImmutable $from, CarbonImmutable $to): array
    {
        if (! $plan->start_date) {
            return [];
        }

        $start = CarbonImmutable::parse($plan->start_date)->startOfDay();
        $cappedTo = $from->addDays(self::MAX_LOOKAHEAD_DAYS);
        if ($to->greaterThan($cappedTo)) {
            $to = $cappedTo;
        }

        if (! $plan->is_recurring || ! $plan->recurrence_type) {
            // Single occurrence
            if ($start->betweenIncluded($from->startOfDay(), $to->endOfDay())) {
                return [$start];
            }
            return [];
        }

        $end = $plan->end_date
            ? CarbonImmutable::parse($plan->end_date)->endOfDay()
            : $to;
        if ($end->greaterThan($to)) {
            $end = $to;
        }

        // Walk from the first occurrence on/after `from`.
        $cursor = $start->greaterThan($from) ? $start : $start;
        // Advance cursor past `from` if needed.
        while ($cursor->lessThan($from->startOfDay())) {
            $cursor = $this->advance($cursor, $plan->recurrence_type);
            if ($cursor->greaterThan($end)) {
                return [];
            }
        }

        $occurrences = [];
        while ($cursor->lessThanOrEqualTo($end)) {
            $occurrences[] = $cursor;
            $cursor = $this->advance($cursor, $plan->recurrence_type);
        }

        return $occurrences;
    }

    private function advance(CarbonImmutable $cursor, string $recurrenceType): CarbonImmutable
    {
        return match ($recurrenceType) {
            'daily' => $cursor->addDay(),
            'weekly' => $cursor->addWeek(),
            'monthly' => $cursor->addMonth(),
            'yearly' => $cursor->addYear(),
            default => $cursor->addYears(100), // unknown type: bail out next loop
        };
    }
}
