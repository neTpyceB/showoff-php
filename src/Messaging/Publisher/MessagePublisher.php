<?php

declare(strict_types=1);

namespace App\Messaging\Publisher;

interface MessagePublisher
{
    public function publish(string $payload): void;
}
