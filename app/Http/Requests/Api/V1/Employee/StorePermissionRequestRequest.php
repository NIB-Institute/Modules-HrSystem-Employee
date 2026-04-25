<?php

namespace Modules\Employee\Http\Requests\Api\V1\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Employee\Models\PermissionRequest;

class StorePermissionRequestRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in(array_keys(PermissionRequest::getTypes())),
            ],
            'reason'    => ['required', 'string', 'min:5', 'max:1000'],
            'from_date' => ['required', 'date', 'after_or_equal:' . now()->subDays(30)->format('Y-m-d')],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Please select a request type',
            'type.in'       => 'Invalid request type selected',
            'reason.required' => 'Please provide a reason for your request',
            'reason.min'      => 'Reason must be at least 5 characters',
            'reason.max'      => 'Reason cannot exceed 1000 characters',
            'from_date.required'       => 'Please select a start date',
            'from_date.after_or_equal' => 'Start date cannot be more than 30 days in the past',
            'to_date.required'         => 'Please select an end date',
            'to_date.after_or_equal'   => 'End date must be on or after the start date',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'request type',
            'from_date' => 'start date',
            'to_date' => 'end date',
        ];
    }
}
