<?php

declare(strict_types=1);

namespace Showoff\Core\Health;

final readonly class HealthCheck
{
    public function __construct(
        public string $name,
        public bool $passed,
        public string $message,
    ) {}
}
