<?php

declare(strict_types=1);

namespace App\Showcase\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('advanced_showcase');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('module_name')
                    ->defaultValue('advanced_showcase')
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('enforce_diagnostics_access')
                    ->defaultTrue()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
