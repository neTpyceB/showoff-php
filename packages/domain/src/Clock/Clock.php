<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Clock;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
