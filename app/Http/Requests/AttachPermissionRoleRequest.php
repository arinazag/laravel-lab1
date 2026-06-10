<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachPermissionRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'permission_id' => [
                'required',
                'exists:permissions,id',
                Rule::unique('permission_role', 'permission_id')
                    ->where('role_id', $this->route('role')->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}