<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Showcase\Application\Processor;

use App\Showcase\Application\Processor\DiagnosticsStageProcessor;
use App\Showcase\Application\Processor\MessagingStageProcessor;
use App\Showcase\Application\Processor\ShowcaseExecutionContext;
use App\Showcase\Application\Processor\ShowcasePipelineRunner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShowcasePipelineRunner::class)]
final class ShowcasePipelineRunnerTest extends TestCase
{
    public function testItRunsAllConfiguredProcessors(): void
    {
        $runner = new ShowcasePipelineRunner([
            new DiagnosticsStageProcessor(),
            new MessagingStageProcessor(),
        ]);

        $results = $runner->run(new ShowcaseExecutionContext(
            moduleName: 'advanced_symfony_showcase',
            triggeredBy: 'test',
        ));

        self::assertCount(2, $results);
        self::assertSame('diagnostics', $results[0]->stage);
        self::assertSame('messaging', $results[1]->stage);
    }
}
