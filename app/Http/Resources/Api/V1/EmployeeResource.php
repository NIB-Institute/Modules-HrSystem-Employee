<?php

namespace Modules\Employee\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'birth_place' => $this->birth_place,
            'current_address' => $this->current_address,
            'avatar_url' => $this->avatar_url,
            'job_title' => $this->job_title,
            'employee_type' => $this->employee_type,
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'salary' => $this->salary,
            'status' => $this->status,
            'is_on_probation' => $this->isOnProbation(),

            // Relationships
            'school' => $this->whenLoaded('school', fn () => [
                'id' => $this->school->id,
                'uuid' => $this->school->uuid ?? null,
                'name' => $this->school->name,
            ]),
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department->id,
                'uuid' => $this->department->uuid ?? null,
                'name' => $this->department->name,
            ]),
            'employee_type_info' => $this->whenLoaded('employeeType', fn () => [
                'id' => $this->employeeType->id,
                'name' => $this->employeeType->name,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
