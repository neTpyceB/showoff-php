<?php

declare(strict_types=1);

namespace App\Showcase\Application\Processor;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('showcase.processor')]
interface ShowcaseStageProcessor
{
    public function name(): string;

    public function process(ShowcaseExecutionContext $context): ShowcaseProcessorResult;
}
