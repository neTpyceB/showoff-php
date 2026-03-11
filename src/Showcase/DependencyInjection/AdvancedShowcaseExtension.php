<?php

declare(strict_types=1);

namespace App\Showcase\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class AdvancedShowcaseExtension extends Extension
{
    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $moduleName = is_string($config['module_name'] ?? null) ? $config['module_name'] : 'advanced_showcase';
        $enforceAccess = (bool) ($config['enforce_diagnostics_access'] ?? true);

        $container->setParameter('advanced_showcase.module_name', $moduleName);
        $container->setParameter('advanced_showcase.enforce_diagnostics_access', $enforceAccess);
    }
}
