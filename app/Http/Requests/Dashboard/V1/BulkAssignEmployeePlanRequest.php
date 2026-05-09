<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkAssignEmployeePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_plan_id' => ['required', 'integer', 'exists:employee_plan,id'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:employees,id', 'distinct'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
