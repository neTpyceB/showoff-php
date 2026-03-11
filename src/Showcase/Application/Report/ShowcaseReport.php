<?php

declare(strict_types=1);

namespace App\Showcase\Application\Report;

use App\Showcase\Application\Processor\ShowcaseProcessorResult;

final readonly class ShowcaseReport
{
    /**
     * @param list<ShowcaseProcessorResult> $results
     */
    public function __construct(
        public string $module,
        public string $generatedAt,
        public array $results,
    ) {}
}
