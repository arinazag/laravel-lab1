<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()->can('update-role')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('update-role');
        }
        return true;
    }

    public function rules(): array
    {
                $roleId = $this->route('role')->id;

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
            'slug' => [
                'required',
                'string',
                'regex:/^[a-z0-9_-]+$/i',
                Rule::unique('roles', 'slug')->ignore($roleId),
            ],
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название роли обязательно',
            'name.unique' => 'Роль с таким названием уже существует',
            'slug.required' => 'Символьный код обязателен',
            'slug.unique' => 'Такой символьный код уже используется',
            'slug.regex' => 'Символьный код может содержать только латинские буквы, цифры, дефис и подчёркивание',
        ];
    }
}