<?php

declare(strict_types=1);

namespace App\Showcase\Application\Processor;

final readonly class ShowcaseExecutionContext
{
    public function __construct(
        public string $moduleName,
        public string $triggeredBy,
    ) {}
}
