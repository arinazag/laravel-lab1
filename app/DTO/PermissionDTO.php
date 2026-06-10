<?php

namespace App\DTO;

use App\Models\Permission;

class PermissionDTO
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

    public static function fromModel(Permission $permission): self
    {
        return new self(
            id: $permission->id,
            name: $permission->name,
            slug: $permission->slug,
            description: $permission->description,
            created_at: $permission->created_at->toISOString(),
            created_by: $permission->created_by,
            deleted_at: $permission->deleted_at?->toISOString(),
            deleted_by: $permission->deleted_by
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}