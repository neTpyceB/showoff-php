<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Config\DatabaseConfig;
use Showoff\Core\Console\Command\AboutCommand;
use Showoff\Core\Health\RuntimeInspector;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(AboutCommand::class)]
final class AboutCommandTest extends TestCase
{
    public function testItPrintsRuntimeMetadata(): void
    {
        $command = new AboutCommand(
            config: new AppConfig(
                appName: 'Core App',
                cliName: 'core-app',
                environment: AppEnvironment::Local,
                debug: true,
                timezone: 'UTC',
                cacheDir: '/tmp/cache',
                logLevel: 'info',
                secret: 'local-development-secret-key',
                buildCommit: 'abcdef1',
                appUrl: 'http://localhost:8080',
                sessionName: 'SHOWOFFSESSID',
                sessionCookieSecure: false,
                database: new DatabaseConfig('mysql', null, 'db', 3306, 'showoff', 'showoff', 'showoff', 'utf8mb4'),
            ),
            runtimeInspector: new CommandRuntimeInspector(),
        );

        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([]));
        self::assertStringContainsString('Core App', $tester->getDisplay());
        self::assertStringContainsString('8.5.3', $tester->getDisplay());
    }
}

final class CommandRuntimeInspector implements RuntimeInspector
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
