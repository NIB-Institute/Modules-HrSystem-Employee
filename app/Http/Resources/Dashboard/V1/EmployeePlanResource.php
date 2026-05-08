<?php

namespace Modules\Employee\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'employee_id' => $this->employee_id,
            'employee_availability_id' => $this->employee_availability_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'start_time' => $this->start_time ? substr((string) $this->start_time, 0, 5) : null,
            'end_time' => $this->end_time ? substr((string) $this->end_time, 0, 5) : null,
            'priority' => $this->priority,
            'location' => $this->location,
            'schedule_mode' => $this->schedule_mode,
            'participants' => $this->participants,
            'is_recurring' => (bool) $this->is_recurring,
            'recurrence_type' => $this->recurrence_type,
            'status' => $this->status,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'uuid' => $this->employee->uuid,
                'full_name' => trim("{$this->employee->first_name} {$this->employee->last_name}"),
                'first_name' => $this->employee->first_name,
                'last_name' => $this->employee->last_name,
                'employee_code' => $this->employee->employee_code,
                'avatar_url' => $this->employee->avatar_url ?? null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
