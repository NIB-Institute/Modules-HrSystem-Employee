<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeePlanAssignmentResource;
use Modules\Employee\Models\EmployeeAvailability;
use Modules\Employee\Models\EmployeePlanAssignment;

class GetEmployeePlanAssignmentEditDataAction
{
    public function execute(EmployeePlanAssignment $assignment): array
    {
        $assignment->load(['plan', 'employee', 'availability']);

        $availabilities = EmployeeAvailability::query()
            ->select('id', 'uuid', 'employee_id', 'day_of_week', 'start_time', 'end_time')
            ->where('employee_id', $assignment->employee_id)
            ->where('is_active', true)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'uuid' => $a->uuid,
                'employee_id' => $a->employee_id,
                'day_of_week' => $a->day_of_week,
                'start_time' => $a->start_time ? substr((string) $a->start_time, 0, 5) : null,
                'end_time' => $a->end_time ? substr((string) $a->end_time, 0, 5) : null,
            ]);

        return [
            'assignment' => (new EmployeePlanAssignmentResource($assignment))->resolve(),
            'availabilities' => $availabilities,
            'statuses' => EmployeePlanAssignmentEnum::STATUSES,
        ];
    }
}
