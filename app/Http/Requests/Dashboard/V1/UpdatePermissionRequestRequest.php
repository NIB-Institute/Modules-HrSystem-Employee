<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Employee\Models\PermissionRequest;

class UpdatePermissionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:' . implode(',', array_keys(PermissionRequest::getTypes()))],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please select a request type.',
            'type.in' => 'Please select a valid request type.',
            'reason.required' => 'Please provide a reason for your request.',
            'reason.min' => 'Reason must be at least 10 characters.',
            'from_date.required' => 'Start date is required.',
            'to_date.required' => 'End date is required.',
            'to_date.after_or_equal' => 'End date must be on or after start date.',
        ];
    }
}
