<?php

namespace App\DTO;

class PermissionCollectionDTO
{
    public function __construct(
        public readonly array $data,
        public readonly int $total,
    ) {}

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'total' => $this->total,
        ];
    }
}