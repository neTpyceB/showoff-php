<?php

declare(strict_types=1);

namespace Showoff\Core\Bootstrap;

use Showoff\Core\Config\ConfigLoader;
use Showoff\Core\Config\ConfigRedactor;
use Showoff\Core\Config\EnvironmentReader;
use Showoff\Core\Console\Command\AboutCommand;
use Showoff\Core\Console\Command\ConfigDumpCommand;
use Showoff\Core\Console\Command\HealthCheckCommand;
use Showoff\Core\Health\NativeDirectoryManager;
use Showoff\Core\Health\NativeRuntimeInspector;
use Showoff\Core\Health\SystemHealthChecker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

final readonly class ApplicationFactory
{
    public function __construct(
        private string $projectRoot,
    ) {}

    public function create(): Application
    {
        require_once $this->projectRoot . '/config/bootstrap.php';

        $config = new ConfigLoader($this->projectRoot)->load(EnvironmentReader::fromGlobals());
        date_default_timezone_set($config->timezone);

        $application = new Application($config->appName);
        $runtimeInspector = new NativeRuntimeInspector();
        $configRedactor = new ConfigRedactor();
        $healthChecker = new SystemHealthChecker(
            runtimeInspector: $runtimeInspector,
            directoryManager: new NativeDirectoryManager(),
        );
        $application->setCommandLoader(new FactoryCommandLoader([
            'app:about' => static fn(): AboutCommand => new AboutCommand($config, $runtimeInspector),
            'app:config:dump' => static fn(): ConfigDumpCommand => new ConfigDumpCommand($config, $configRedactor),
            'app:health:check' => static fn(): HealthCheckCommand => new HealthCheckCommand($config, $healthChecker),
        ]));

        return $application;
    }
}
