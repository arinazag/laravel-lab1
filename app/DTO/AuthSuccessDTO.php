<?php

namespace App\DTO;

class AuthSuccessDTO
{
    public function __construct(
        public readonly string $access_token,   // токен доступа (60 минут)
        public readonly string $refresh_token,  // токен обновления (7 дней)
        public readonly UserDTO $user,          // данные пользователя
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
