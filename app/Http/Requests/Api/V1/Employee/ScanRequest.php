<?php

namespace Modules\Employee\Http\Requests\Api\V1\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:school_departments,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
            'scan_method' => ['nullable', 'string', 'in:qr,nfc,manual,gps'],
            'device_info' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required' => 'Department ID is required.',
            'department_id.exists' => 'The selected department does not exist.',
            'latitude.required' => 'Latitude is required for location verification.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.required' => 'Longitude is required for location verification.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'scan_method.in' => 'Invalid scan method. Must be qr, nfc, manual, or gps.',
        ];
    }
}
