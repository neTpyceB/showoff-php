<?php

declare(strict_types=1);

namespace App\Realtime\Publisher;

interface RealtimePublisher
{
    /**
     * @param array<string, mixed> $payload
     */
    public function publish(string $topic, array $payload): void;
}
