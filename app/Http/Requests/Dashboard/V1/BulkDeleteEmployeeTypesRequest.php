<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteEmployeeTypesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid', 'exists:employee_types,uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'uuids.required' => 'Please select at least one employee type to delete.',
            'uuids.array' => 'Invalid selection format.',
            'uuids.min' => 'Please select at least one employee type to delete.',
            'uuids.*.exists' => 'One or more selected employee types do not exist.',
        ];
    }
}
