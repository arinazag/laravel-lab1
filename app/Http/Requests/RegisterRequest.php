<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'regex:/^[A-Z][a-zA-Z]{6,}$/',
                Rule::unique('users')->where(function ($query) {
                    return $query->whereRaw('LOWER(username) = ?', [strtolower($this->username)]);
                }),
            ],
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|regex:/^(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).*$/',
            'c_password' => 'required|same:password',
            'birthday' => [
                'required',
                'date',
                'format:Y-m-d',
                function ($attribute, $value, $fail) {
                    $age = Carbon::parse($value)->age;
                    if ($age < 14) {
                        $fail('You must be at least 14 years old to register.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username must start with a capital letter and contain at least 7 letters (only latin)',
            'username.unique' => 'This username is already taken',
            'email.unique' => 'This email is already registered',
            'password.regex' => 'Password must contain at least one digit, one special character, one uppercase and one lowercase letter',
            'password.min' => 'Password must be at least 8 characters',
            'c_password.same' => 'Password confirmation does not match',
            'birthday.date' => 'Birthday must be a valid date in YYYY-MM-DD format',
            'birthday.required' => 'Birthday is required',
        ];
    }
}