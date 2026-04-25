<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteAttendancesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid', 'exists:attendances,uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'uuids.required' => 'Please select at least one attendance record to delete.',
            'uuids.array' => 'Invalid selection format.',
            'uuids.min' => 'Please select at least one attendance record to delete.',
            'uuids.*.exists' => 'One or more selected attendance records do not exist.',
        ];
    }
}
