<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Employee\Models\Attendance;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $statuses = implode(',', array_keys(Attendance::getStatuses()));

        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_date' => ['required', 'date'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i', 'after:check_in_time'],
            'status' => ['required', 'in:' . $statuses],
            'department_id' => ['nullable', 'exists:school_departments,id'],
            'classroom_id' => ['nullable', 'exists:school_classrooms,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Please select an employee.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'attendance_date.required' => 'Please select a date.',
            'check_out_time.after' => 'Check-out time must be after check-in time.',
        ];
    }
}
