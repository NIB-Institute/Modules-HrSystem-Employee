<?php

namespace Modules\Employee\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;

class EmployeePlanAssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'employee_plan_id' => $this->employee_plan_id,
            'employee_id' => $this->employee_id,
            'employee_availability_id' => $this->employee_availability_id,
            'status' => $this->status,
            'status_label' => EmployeePlanAssignmentEnum::statusLabel((string) $this->status),
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'notes' => $this->notes,

            'plan' => $this->whenLoaded('plan', fn () => [
                'id' => $this->plan->id,
                'uuid' => $this->plan->uuid,
                'title' => $this->plan->title,
                'priority' => $this->plan->priority,
                'status' => $this->plan->status,
                'start_date' => $this->plan->start_date?->format('Y-m-d'),
                'end_date' => $this->plan->end_date?->format('Y-m-d'),
                'start_time' => $this->plan->start_time
                    ? substr((string) $this->plan->start_time, 0, 5)
                    : null,
                'end_time' => $this->plan->end_time
                    ? substr((string) $this->plan->end_time, 0, 5)
                    : null,
            ]),
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'uuid' => $this->employee->uuid,
                'full_name' => trim("{$this->employee->first_name} {$this->employee->last_name}"),
                'first_name' => $this->employee->first_name,
                'last_name' => $this->employee->last_name,
                'employee_code' => $this->employee->employee_code,
                'avatar_url' => $this->employee->avatar_url ?? null,
            ]),
            'availability' => $this->whenLoaded('availability', fn () => $this->availability ? [
                'id' => $this->availability->id,
                'uuid' => $this->availability->uuid,
                'day_of_week' => $this->availability->day_of_week,
                'start_time' => $this->availability->start_time
                    ? substr((string) $this->availability->start_time, 0, 5)
                    : null,
                'end_time' => $this->availability->end_time
                    ? substr((string) $this->availability->end_time, 0, 5)
                    : null,
                'is_active' => (bool) $this->availability->is_active,
            ] : null),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
