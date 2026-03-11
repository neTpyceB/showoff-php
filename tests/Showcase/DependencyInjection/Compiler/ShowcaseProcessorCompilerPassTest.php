<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Showcase\DependencyInjection\Compiler;

use App\Showcase\Application\Processor\ShowcasePipelineRunner;
use App\Showcase\DependencyInjection\Compiler\ShowcaseProcessorCompilerPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(ShowcaseProcessorCompilerPass::class)]
final class ShowcaseProcessorCompilerPassTest extends TestCase
{
    public function testItInjectsTaggedProcessorsByPriority(): void
    {
        $container = new ContainerBuilder();
        $container->register(ShowcasePipelineRunner::class, ShowcasePipelineRunner::class)
            ->setArgument('$processors', []);
        $container->register('showcase.processor.low', \stdClass::class)
            ->addTag('showcase.processor', ['priority' => 10]);
        $container->register('showcase.processor.high', \stdClass::class)
            ->addTag('showcase.processor', ['priority' => 100]);

        $pass = new ShowcaseProcessorCompilerPass();
        $pass->process($container);

        $processors = $container->findDefinition(ShowcasePipelineRunner::class)->getArgument('$processors');
        self::assertIsArray($processors);
        self::assertCount(2, $processors);
        self::assertInstanceOf(Reference::class, $processors[0]);
        self::assertInstanceOf(Reference::class, $processors[1]);
        self::assertSame('showcase.processor.high', (string) $processors[0]);
        self::assertSame('showcase.processor.low', (string) $processors[1]);
    }
}
