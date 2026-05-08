<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Employee\Enums\EmployeePlanEnum;

class StoreEmployeePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'employee_availability_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'priority' => ['required', Rule::in(EmployeePlanEnum::PRIORITIES)],
            'location' => ['nullable', 'string', 'max:255'],
            'schedule_mode' => ['required', Rule::in(EmployeePlanEnum::SCHEDULE_MODES)],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['integer', 'exists:employees,id'],
            'is_recurring' => ['boolean'],
            'recurrence_type' => ['nullable', 'required_if:is_recurring,true', Rule::in(EmployeePlanEnum::RECURRENCE_TYPES)],
            'status' => ['nullable', Rule::in(EmployeePlanEnum::STATUSES)],
        ];
    }
}
