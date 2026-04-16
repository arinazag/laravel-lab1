<?php

namespace App\DTO;

class TokenListDTO
{
    public function __construct(
        public readonly array $tokens,
    ) {}

    public function toArray(): array
    {
        return [
            'tokens' => $this->tokens,
        ];
    }
}