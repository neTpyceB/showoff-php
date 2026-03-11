<?php

declare(strict_types=1);

namespace App\Realtime\Publisher;

final class NullRealtimePublisher implements RealtimePublisher
{
    public function publish(string $topic, array $payload): void {}
}
