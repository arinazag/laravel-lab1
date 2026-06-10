<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetachUserRoleRequest extends FormRequest
{
    public function authorize()
    {
        if (!$this->user()->can('delete-user')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('delete-user');
        }
        return true;
    }

    public function rules()
    {
        return [];
    }
}