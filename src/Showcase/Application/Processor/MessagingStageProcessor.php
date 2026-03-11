<?php

declare(strict_types=1);

namespace App\Showcase\Application\Processor;

final readonly class MessagingStageProcessor implements ShowcaseStageProcessor
{
    public function name(): string
    {
        return 'messaging';
    }

    public function process(ShowcaseExecutionContext $context): ShowcaseProcessorResult
    {
        return new ShowcaseProcessorResult(
            stage: $this->name(),
            payload: [
                'transport' => 'symfony_messenger',
                'module' => $context->moduleName,
                'ready' => true,
            ],
        );
    }
}
