<?php

namespace App\DTO;

class AuthSuccessDTO
{
    public function __construct(
        public readonly string $access_token,
        public readonly string $refresh_token,
        public readonly UserDTO $user,
    ) {}

    public function toArray(): array
    {
        return [
            'access_token' => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'user' => $this->user->toArray(),
        ];
    }
}
