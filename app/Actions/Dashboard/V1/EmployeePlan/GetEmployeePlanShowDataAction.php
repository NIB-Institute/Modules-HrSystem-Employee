<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlan;

use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeePlanAssignmentResource;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeePlanResource;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanAssignment;

class GetEmployeePlanShowDataAction
{
    public function execute(EmployeePlan $plan, int $perPage = 15): array
    {
        $plan->loadCount('assignments');

        $assignments = $plan->assignments()
            ->with(['employee', 'availability'])
            ->latest('assigned_at')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => $plan->assignments_count,
            'assigned'    => EmployeePlanAssignment::where('employee_plan_id', $plan->id)
                ->where('status', EmployeePlanAssignmentEnum::STATUS_ASSIGNED)->count(),
            'in_progress' => EmployeePlanAssignment::where('employee_plan_id', $plan->id)
                ->where('status', EmployeePlanAssignmentEnum::STATUS_IN_PROGRESS)->count(),
            'completed'   => EmployeePlanAssignment::where('employee_plan_id', $plan->id)
                ->where('status', EmployeePlanAssignmentEnum::STATUS_COMPLETED)->count(),
            'expired'     => EmployeePlanAssignment::where('employee_plan_id', $plan->id)
                ->where('status', EmployeePlanAssignmentEnum::STATUS_EXPIRED)->count(),
        ];

        return [
            'plan' => (new EmployeePlanResource($plan))->resolve(),
            'assignments' => [
                'data' => EmployeePlanAssignmentResource::collection($assignments)->resolve(),
                'meta' => [
                    'current_page' => $assignments->currentPage(),
                    'last_page'    => $assignments->lastPage(),
                    'per_page'     => $assignments->perPage(),
                    'total'        => $assignments->total(),
                ],
            ],
            'stats' => $stats,
        ];
    }
}
