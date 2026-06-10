<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()->can('update-permission')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('update-permission');
        }
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')->id;

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('permissions', 'name')->ignore($permissionId),
            ],
            'slug' => [
                'required',
                'string',
                'regex:/^[a-z0-9_-]+$/i',
                Rule::unique('permissions', 'slug')->ignore($permissionId),
            ],
            'description' => 'nullable|string',
        ];
    }
}