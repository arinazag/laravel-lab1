<?php

namespace App\Http\Requests;

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
            'username' => 'required|string|regex:/^[A-Z][a-zA-Z]{6,}$/',
            'password' => 'required|string|min:8|regex:/^(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).*$/',
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username must start with a capital letter and contain at least 7 letters (only latin)',
            'password.regex' => 'Password must contain at least one digit, one special character, one uppercase and one lowercase letter',
            'password.min' => 'Password must be at least 8 characters',
        ];
    }
}