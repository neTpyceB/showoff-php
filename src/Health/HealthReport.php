<?php

declare(strict_types=1);

namespace Showoff\Core\Health;

final readonly class HealthReport
{
    /**
     * @param list<HealthCheck> $checks
     */
    public function __construct(
        public array $checks,
    ) {}

    public function isHealthy(): bool
    {
        foreach ($this->checks as $check) {
            if (!$check->passed) {
                return false;
            }
        }

        return true;
    }
}
