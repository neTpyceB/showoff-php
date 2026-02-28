<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Console\Command\HealthCheckCommand;
use Showoff\Core\Health\DirectoryManager;
use Showoff\Core\Health\RuntimeInspector;
use Showoff\Core\Health\SystemHealthChecker;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(HealthCheckCommand::class)]
final class HealthCheckCommandTest extends TestCase
{
    public function testItReportsSuccessForHealthySystems(): void
    {
        $checker = new SystemHealthChecker(
            runtimeInspector: new HealthCommandRuntimeInspector(),
            directoryManager: new HealthCommandDirectoryManager(),
        );
        $command = new HealthCheckCommand($this->config(), $checker);

        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([]));
        self::assertStringContainsString('All health checks passed.', $tester->getDisplay());
    }

    private function config(): AppConfig
    {
        return new AppConfig(
            appName: 'Core App',
            cliName: 'core-app',
            environment: AppEnvironment::Local,
            debug: true,
            timezone: 'UTC',
            cacheDir: '/tmp/cache',
            logLevel: 'info',
            secret: 'local-development-secret-key',
            buildCommit: null,
        );
    }
}

final class HealthCommandRuntimeInspector implements RuntimeInspector
{
    public function phpVersion(): string
    {
        return '8.5.3';
    }

    public function isExtensionLoaded(string $extension): bool
    {
        return true;
    }
}

final class HealthCommandDirectoryManager implements DirectoryManager
{
    public function ensureWritable(string $path): bool
    {
        return true;
    }
}
