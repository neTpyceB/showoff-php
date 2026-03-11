<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Showcase\Infrastructure\Console;

use App\Showcase\Application\Processor\DiagnosticsStageProcessor;
use App\Showcase\Application\Processor\ShowcasePipelineRunner;
use App\Showcase\Infrastructure\Console\RunShowcasePipelineCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(RunShowcasePipelineCommand::class)]
final class RunShowcasePipelineCommandTest extends TestCase
{
    public function testItRunsPipelineAndPrintsResults(): void
    {
        $runner = new ShowcasePipelineRunner([new DiagnosticsStageProcessor()]);
        $command = new RunShowcasePipelineCommand($runner, 'advanced_symfony_showcase');
        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([]));
        self::assertStringContainsString('module=advanced_symfony_showcase processors=1', $tester->getDisplay());
        self::assertStringContainsString('[diagnostics]', $tester->getDisplay());
    }
}
