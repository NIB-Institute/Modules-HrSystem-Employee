<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;

class StoreEmployeePlanAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_plan_id' => ['required', 'integer', 'exists:employee_plan,id'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'employee_availability_id' => ['nullable', 'integer', 'exists:employee_availability,id'],
            'status' => ['nullable', Rule::in(EmployeePlanAssignmentEnum::STATUSES)],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
