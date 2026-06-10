<?php

namespace App\DTO;

use App\Models\Role;

class RoleDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly string $created_at,
        public readonly int $created_by,
        public readonly ?string $deleted_at = null,
        public readonly ?int $deleted_by = null,
    ) {}

    public static function fromModel(Role $role): self
    {
        return new self(
            id: $role->id,
            name: $role->name,
            slug: $role->slug,
            description: $role->description,
            created_at: $role->created_at->toISOString(),
            created_by: $role->created_by,
            deleted_at: $role->deleted_at?->toISOString(),
            deleted_by: $role->deleted_by
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}