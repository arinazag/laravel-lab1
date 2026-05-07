<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|regex:/^(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).*$/',
            'c_password' => 'required|same:new_password',
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.min' => 'Password must be at least 8 characters',
            'new_password.regex' => 'Password must contain at least one digit, one special character, one uppercase and one lowercase letter',
            'c_password.same' => 'Password confirmation does not match',
        ];
    }
}