<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Clock;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
