<?php

declare(strict_types=1);

namespace App\Showcase\Application\Messaging;

final readonly class ShowcaseAuditMessage
{
    public function __construct(
        public string $action,
        public string $occurredAt,
    ) {}
}
