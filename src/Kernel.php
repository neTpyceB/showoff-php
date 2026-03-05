<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $configDir = $this->getProjectDir() . '/config';
        $loader->load($configDir . '/packages/*.yaml', 'glob');

        $environmentPackagesDir = $configDir . '/packages/' . $this->environment;
        if (is_dir($environmentPackagesDir)) {
            $loader->load($environmentPackagesDir . '/*.yaml', 'glob');
        }

        $loader->load($configDir . '/services.yaml');

        $environmentServicesFile = $configDir . '/services_' . $this->environment . '.yaml';
        if (is_file($environmentServicesFile)) {
            $loader->load($environmentServicesFile);
        }
    }

    protected function configureRoutes(\Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator $routes): void
    {
        $routes->import('../config/routes.yaml');
    }
}
