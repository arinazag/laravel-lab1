<?php

namespace App\DTO;

class DatabaseInfoDTO
{
    public function __construct(
        public readonly string $connection,
        public readonly string $driver,
        public readonly ?string $databaseName,
        public readonly string $serverVersion
    ) {}

    public function toArray(): array
    {
        return [
            'connection' => $this->connection,
            'driver' => $this->driver,
            'database_name' => $this->databaseName,
            'server_version' => $this->serverVersion,
        ];
    }
}