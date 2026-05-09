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

        // Active slots for this employee.
        $availabilities = EmployeeAvailability::query()
            ->select('id', 'uuid', 'employee_id', 'day_of_week', 'start_time', 'end_time', 'is_active')
            ->where('employee_id', $assignment->employee_id)
            ->where('is_active', true)
            ->get();

        // If the assignment is bound to a slot that's now inactive (or
        // soft-deleted), prepend it so the user doesn't lose the binding
        // when editing. Marked is_active=false so the UI can flag it.
        if ($assignment->employee_availability_id
            && !$availabilities->contains('id', $assignment->employee_availability_id)
        ) {
            $existing = EmployeeAvailability::withTrashed()
                ->select('id', 'uuid', 'employee_id', 'day_of_week', 'start_time', 'end_time', 'is_active')
                ->find($assignment->employee_availability_id);

            if ($existing) {
                $availabilities->prepend($existing);
            }
        }

        $availabilities = $availabilities->map(fn ($a) => [
            'id' => $a->id,
            'uuid' => $a->uuid,
            'employee_id' => $a->employee_id,
            'day_of_week' => $a->day_of_week,
            'start_time' => $a->start_time ? substr((string) $a->start_time, 0, 5) : null,
            'end_time' => $a->end_time ? substr((string) $a->end_time, 0, 5) : null,
            'is_active' => (bool) $a->is_active,
        ])->values();

        return [
            'assignment' => (new EmployeePlanAssignmentResource($assignment))->resolve(),
            'availabilities' => $availabilities,
            'statuses' => EmployeePlanAssignmentEnum::STATUSES,
        ];
    }
}
