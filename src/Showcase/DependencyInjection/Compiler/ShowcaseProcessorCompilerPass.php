<?php

declare(strict_types=1);

namespace App\Showcase\DependencyInjection\Compiler;

use App\Showcase\Application\Processor\ShowcasePipelineRunner;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ShowcaseProcessorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ShowcasePipelineRunner::class)) {
            return;
        }

        $tagged = $container->findTaggedServiceIds('showcase.processor');
        if ($tagged === []) {
            return;
        }

        $processors = [];
        foreach ($tagged as $serviceId => $tags) {
            $highestPriority = 0;
            foreach ($tags as $tag) {
                if (!is_array($tag)) {
                    continue;
                }

                $priority = $tag['priority'] ?? 0;
                if (is_int($priority) || is_string($priority) && is_numeric($priority)) {
                    $highestPriority = max($highestPriority, (int) $priority);
                }
            }

            $processors[] = [
                'priority' => $highestPriority,
                'reference' => new Reference($serviceId),
            ];
        }

        usort($processors, static fn(array $left, array $right): int => $right['priority'] <=> $left['priority']);

        $definition = $container->findDefinition(ShowcasePipelineRunner::class);
        $definition->setArgument('$processors', array_map(
            static fn(array $item): Reference => $item['reference'],
            $processors,
        ));
    }
}
