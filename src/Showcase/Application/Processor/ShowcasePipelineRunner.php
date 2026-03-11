<?php

declare(strict_types=1);

namespace App\Showcase\Application\Processor;

final readonly class ShowcasePipelineRunner
{
    /**
     * @param list<ShowcaseStageProcessor> $processors
     */
    public function __construct(
        private array $processors = [],
    ) {}

    /**
     * @return list<ShowcaseProcessorResult>
     */
    public function run(ShowcaseExecutionContext $context): array
    {
        $results = [];
        foreach ($this->processors as $processor) {
            $results[] = $processor->process($context);
        }

        return $results;
    }
}
