<?php

namespace Modules\Employee\Http\Requests\Api\V1\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'first_name' => ['sometimes', 'string', 'min:2', 'max:100'],
            'last_name' => ['sometimes', 'string', 'min:2', 'max:100'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'birth_place' => ['sometimes', 'nullable', 'string', 'max:255'],
            'current_address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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
            'first_name.min' => 'First name must be at least 2 characters',
            'first_name.max' => 'First name cannot exceed 100 characters',
            'last_name.min' => 'Last name must be at least 2 characters',
            'last_name.max' => 'Last name cannot exceed 100 characters',
            'phone_number.max' => 'Phone number cannot exceed 20 characters',
            'gender.in' => 'Invalid gender selected',
            'date_of_birth.before' => 'Date of birth must be before today',
            'current_address.max' => 'Address cannot exceed 500 characters',
            'avatar.image' => 'Avatar must be an image',
            'avatar.mimes' => 'Avatar must be a jpeg, png, jpg, or gif',
            'avatar.max' => 'Avatar cannot exceed 2MB',
        ];
    }
}
