<?php

declare(strict_types=1);

namespace Showoff\Core\Bootstrap\Factory;

use Psr\Container\ContainerInterface;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Console\Command\AboutCommand;
use Showoff\Core\Console\Command\ConfigDumpCommand;
use Showoff\Core\Console\Command\DatabaseMigrateCommand;
use Showoff\Core\Console\Command\DatabaseStatusCommand;
use Showoff\Core\Console\Command\HealthCheckCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

final readonly class ConsoleApplicationFactory
{
    public function __construct(
        private AppConfig $config,
        private ContainerInterface $container,
    ) {}

    public function create(): Application
    {
        $application = new Application($this->config->appName);
        $application->setCommandLoader(new ContainerCommandLoader($this->container, [
            'app:about' => AboutCommand::class,
            'app:config:dump' => ConfigDumpCommand::class,
            'app:database:migrate' => DatabaseMigrateCommand::class,
            'app:database:status' => DatabaseStatusCommand::class,
            'app:health:check' => HealthCheckCommand::class,
        ]));

        return $application;
    }
}
