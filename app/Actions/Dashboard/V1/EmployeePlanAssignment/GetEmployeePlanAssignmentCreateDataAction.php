<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeAvailability;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanAssignment;

class GetEmployeePlanAssignmentCreateDataAction
{
    public function execute(?int $planId = null, ?int $employeeId = null): array
    {
        $plans = EmployeePlan::query()
            ->select('id', 'uuid', 'title', 'start_date', 'end_date', 'start_time', 'end_time', 'status')
            ->orderByDesc('start_date')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'title' => $p->title,
                'start_date' => $p->start_date?->format('Y-m-d'),
                'end_date' => $p->end_date?->format('Y-m-d'),
                'start_time' => $p->start_time ? substr((string) $p->start_time, 0, 5) : null,
                'end_time' => $p->end_time ? substr((string) $p->end_time, 0, 5) : null,
                'status' => $p->status,
            ]);

        $employees = Employee::query()
            ->select('id', 'uuid', 'first_name', 'last_name', 'employee_code')
            ->where('status', true)
            ->orderBy('first_name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'uuid' => $e->uuid,
                'full_name' => trim("{$e->first_name} {$e->last_name}"),
                'employee_code' => $e->employee_code,
            ]);

        $availabilities = EmployeeAvailability::query()
            ->select('id', 'uuid', 'employee_id', 'day_of_week', 'start_time', 'end_time', 'is_active')
            ->where('is_active', true)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'uuid' => $a->uuid,
                'employee_id' => $a->employee_id,
                'day_of_week' => $a->day_of_week,
                'start_time' => $a->start_time ? substr((string) $a->start_time, 0, 5) : null,
                'end_time' => $a->end_time ? substr((string) $a->end_time, 0, 5) : null,
                'is_active' => true,
            ]);

        // Map of plan_id => [employee_ids already assigned], so the frontend can
        // grey-out / skip already-assigned employees per plan and prevent the
        // user from picking duplicates (which would result in 0 created).
        $existingAssignments = EmployeePlanAssignment::query()
            ->whereIn('status', [
                EmployeePlanAssignmentEnum::STATUS_ASSIGNED,
                EmployeePlanAssignmentEnum::STATUS_IN_PROGRESS,
            ])
            ->get(['employee_plan_id', 'employee_id'])
            ->groupBy('employee_plan_id')
            ->map(fn ($rows) => $rows->pluck('employee_id')->values()->all())
            ->toArray();

        return [
            'plans' => $plans,
            'employees' => $employees,
            'availabilities' => $availabilities,
            'existingAssignments' => $existingAssignments,
            'statuses' => EmployeePlanAssignmentEnum::STATUSES,
            'selectedPlanId' => $planId,
            'selectedEmployeeId' => $employeeId,
        ];
    }
}
