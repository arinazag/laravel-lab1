<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachUserRoleRequest extends FormRequest
{
    public function authorize()
    {
        if (!$this->user()->can('update-user')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('update-user');
        }
        return true;
    }

    public function rules()
    {
        return [
            'role_id' => [
                'required',
                'exists:roles,id',
                Rule::unique('role_user', 'role_id')
                    ->where('user_id', $this->route('user')->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}