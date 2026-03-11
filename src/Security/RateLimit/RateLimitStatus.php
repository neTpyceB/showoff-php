<?php

declare(strict_types=1);

namespace App\Security\RateLimit;

final readonly class RateLimitStatus
{
    public function __construct(
        public bool $blocked,
        public int $attemptsRemaining,
        public int $retryAfterSeconds,
    ) {}
}
