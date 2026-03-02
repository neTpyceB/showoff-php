<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Config\ConfigRedactor;
use Showoff\Core\Console\Command\ConfigDumpCommand;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(ConfigDumpCommand::class)]
final class ConfigDumpCommandTest extends TestCase
{
    public function testItPrintsRedactedConfiguration(): void
    {
        $command = new ConfigDumpCommand(
            config: new AppConfig(
                appName: 'Core App',
                cliName: 'core-app',
                environment: AppEnvironment::Local,
                debug: true,
                timezone: 'UTC',
                cacheDir: '/tmp/cache',
                logLevel: 'info',
                secret: 'local-development-secret-key',
                buildCommit: null,
                appUrl: 'http://localhost:8080',
                sessionName: 'SHOWOFFSESSID',
                sessionCookieSecure: false,
            ),
            configRedactor: new ConfigRedactor(),
        );

        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([]));
        self::assertStringContainsString('"app_name": "Core App"', $tester->getDisplay());
        self::assertStringContainsString('"secret": "[REDACTED]"', $tester->getDisplay());
    }
}
