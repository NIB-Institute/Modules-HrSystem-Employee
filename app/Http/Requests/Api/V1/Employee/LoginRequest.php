<?php

namespace Modules\Employee\Http\Requests\Api\V1\Employee;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Email is required when phone is not provided.',
            'email.email' => 'Please provide a valid email address.',
            'phone.required_without' => 'Phone is required when email is not provided.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ];
    }
}
