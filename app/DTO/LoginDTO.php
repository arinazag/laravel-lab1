<?php

namespace App\DTO;

class LoginDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
    ) {}
}