<?php

namespace Modules\Employee\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Employee\Enums\EmployeeAvailabilityEnum;

class EmployeeAvailabilityResource extends JsonResource
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
            'day_of_week' => $this->day_of_week,
            'day_label' => EmployeeAvailabilityEnum::dayLabel((string) $this->day_of_week),
            'start_time' => $this->start_time ? substr((string) $this->start_time, 0, 5) : null,
            'end_time' => $this->end_time ? substr((string) $this->end_time, 0, 5) : null,
            'is_active' => (bool) $this->is_active,
            'notes' => $this->notes,
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
