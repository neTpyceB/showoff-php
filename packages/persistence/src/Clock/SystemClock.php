<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Clock;

final readonly class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
