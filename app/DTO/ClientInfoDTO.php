<?php

namespace App\DTO;

class ClientInfoDTO
{
    public function __construct(
        public readonly string $ipAddress,
        public readonly string $userAgent,
        public readonly string $requestMethod,
        public readonly string $requestUri
    ) {}

    public function toArray(): array
    {
        return [
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'request_method' => $this->requestMethod,
            'request_uri' => $this->requestUri,
        ];
    }
}