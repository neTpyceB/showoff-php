<?php

declare(strict_types=1);

namespace App\Showcase\Application\Security;

final readonly class ShowcaseCapability
{
    public function __construct(
        public bool $diagnosticsEnabled,
    ) {}
}
