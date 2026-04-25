<?php

namespace App\Services\Orchestration\Contracts;

interface WorkerDriver
{
    public function run(string $worker, array $payload): array;
}

