<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestoreUserRoleRequest extends FormRequest
{
    public function authorize()
    {
        if (!$this->user()->can('restore-user')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('restore-user');
        }
        return true;
    }

    public function rules()
    {
        return [];
    }
}