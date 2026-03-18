<?php

namespace App\DTO;

class ServerInfoDTO
{
    public function __construct(
        public readonly string $phpVersion,
        public readonly string $serverSoftware,
        public readonly string $phpSapi,
        public readonly int $maxExecutionTime,
        public readonly int $memoryLimit
    ) {}

    public function toArray(): array
    {
        return [
            'php_version' => $this->phpVersion,
            'server_software' => $this->serverSoftware,
            'php_sapi' => $this->phpSapi,
            'max_execution_time' => $this->maxExecutionTime,
            'memory_limit' => $this->memoryLimit,
        ];
    }
}