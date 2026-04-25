<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Employee\Models\Attendance;

class UpdateAttendanceRequest extends FormRequest
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
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'status' => ['nullable', 'in:' . $statuses],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
