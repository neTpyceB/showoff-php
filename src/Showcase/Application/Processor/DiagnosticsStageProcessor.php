<?php

declare(strict_types=1);

namespace App\Showcase\Application\Processor;

final readonly class DiagnosticsStageProcessor implements ShowcaseStageProcessor
{
    public function name(): string
    {
        return 'diagnostics';
    }

    public function process(ShowcaseExecutionContext $context): ShowcaseProcessorResult
    {
        return new ShowcaseProcessorResult(
            stage: $this->name(),
            payload: [
                'module' => $context->moduleName,
                'status' => 'ok',
                'triggeredBy' => $context->triggeredBy,
            ],
        );
    }
}
