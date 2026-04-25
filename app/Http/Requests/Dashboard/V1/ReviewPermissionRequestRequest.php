<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReviewPermissionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,reject'],
            'review_note' => ['nullable', 'string', 'max:500'],
            'rejected_status' => ['boolean'],
            'rejected_reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Please select an action.',
            'action.in' => 'Action must be either approve or reject.',
            'review_note.max' => 'Review note must not exceed 500 characters.',
            'rejected_reason.max' => 'Inject reason must not exceed 500 characters.',
        ];
    }
}
