<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\DTO\PermissionDTO;

class StorePermissionRequest extends FormRequest
{
    public function authorize()
    {
        if (!$this->user()->can('create-permission')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('create-permission');
        }
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:permissions,name',
            'slug' => 'required|string|unique:permissions,slug|regex:/^[a-z0-9_-]+$/i',
            'description' => 'nullable|string',
        ];
    }

    public function toDTO(): PermissionDTO
    {
        return new PermissionDTO(
            id: 0,
            name: $this->name,
            slug: $this->slug,
            description: $this->description,
            created_at: now()->toISOString(),
            created_by: $this->user()->id,
        );
    }
}