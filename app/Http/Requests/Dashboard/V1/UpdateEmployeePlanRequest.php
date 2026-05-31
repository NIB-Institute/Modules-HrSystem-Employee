<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Employee\Enums\EmployeePlanEnum;

class UpdateEmployeePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'priority' => ['sometimes', 'required', Rule::in(EmployeePlanEnum::PRIORITIES)],
            'location' => ['nullable', 'string', 'max:255'],
            'telegram_group_chat_id' => ['nullable', 'string', 'max:255'],
            'telegram_group_name' => ['nullable', 'string', 'max:255'],
            'schedule_mode' => ['sometimes', 'required', Rule::in(EmployeePlanEnum::SCHEDULE_MODES)],
            'is_recurring' => ['boolean'],
            'recurrence_type' => ['nullable', 'required_if:is_recurring,true', Rule::in(EmployeePlanEnum::RECURRENCE_TYPES)],
            'status' => ['nullable', Rule::in(EmployeePlanEnum::STATUSES)],
            'valid_for_months' => ['nullable', 'integer', 'min:1', 'max:120'],
        ];
    }
}
