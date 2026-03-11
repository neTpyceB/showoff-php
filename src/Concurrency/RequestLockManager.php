<?php

declare(strict_types=1);

namespace App\Concurrency;

interface RequestLockManager
{
    public function acquire(string $key, int $ttlSeconds): bool;

    public function release(string $key): void;
}
