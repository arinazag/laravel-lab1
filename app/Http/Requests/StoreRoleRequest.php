<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\DTO\RoleDTO;

class StoreRoleRequest extends FormRequest
{
    public function authorize()
    {
        if (!$this->user()->can('create-role')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('create-role');
        }
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:roles,name',
            'slug' => 'required|string|unique:roles,slug|regex:/^[a-z0-9_-]+$/i',
            'description' => 'nullable|string',
        ];
    }

    public function toDTO(): RoleDTO
    {
        return new RoleDTO(
            id: 0,
            name: $this->name,
            slug: $this->slug,
            description: $this->description,
            created_at: now()->toISOString(),
            created_by: $this->user()->id,
        );
    }
}