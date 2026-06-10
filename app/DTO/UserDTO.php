<?php

namespace App\DTO;

use App\Models\User;

class UserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $birthday,
        public readonly string $created_at,
        public readonly ?string $updated_at = null,
        public readonly array $roles = [],   
    ) {}

    public static function fromModel(User $user, array $roles = []): self
    {
        return new self(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday,
            created_at: $user->created_at->toISOString(),
            updated_at: $user->updated_at?->toISOString(),
            roles: $roles,
        );
    }
}