<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Health;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Health\DirectoryManager;
use Showoff\Core\Health\RuntimeInspector;
use Showoff\Core\Health\SystemHealthChecker;

#[CoversClass(SystemHealthChecker::class)]
final class SystemHealthCheckerTest extends TestCase
{
    public function testItBuildsAHealthyReportWhenAllChecksPass(): void
    {
        $checker = new SystemHealthChecker(
            runtimeInspector: new StubRuntimeInspector('8.5.3', ['json' => true, 'mbstring' => true]),
            directoryManager: new StubDirectoryManager(true),
        );

        $report = $checker->check($this->config('/tmp/cache'));

        self::assertTrue($report->isHealthy());
        self::assertCount(4, $report->checks);
    }

    public function testItBuildsAnUnhealthyReportWhenAnyCheckFails(): void
    {
        $checker = new SystemHealthChecker(
            runtimeInspector: new StubRuntimeInspector('8.4.9', ['json' => true, 'mbstring' => false]),
            directoryManager: new StubDirectoryManager(false),
        );

        $report = $checker->check($this->config('/tmp/cache'));

        self::assertFalse($report->isHealthy());
        self::assertSame('PHP runtime', $report->checks[0]->name);
        self::assertFalse($report->checks[0]->passed);
    }

    private function config(string $cacheDir): AppConfig
    {
        return new AppConfig(
            appName: 'Core App',
            cliName: 'core-app',
            environment: AppEnvironment::Local,
            debug: true,
            timezone: 'UTC',
            cacheDir: $cacheDir,
            logLevel: 'info',
            secret: 'local-development-secret-key',
            buildCommit: null,
        );
    }
}

final readonly class StubRuntimeInspector implements RuntimeInspector
{
    /**
     * @param array<string, bool> $extensions
     */
    public function __construct(
        private string $phpVersion,
        private array $extensions,
    ) {}

    public function phpVersion(): string
    {
        return $this->phpVersion;
    }

    public function isExtensionLoaded(string $extension): bool
    {
        return $this->extensions[$extension] ?? false;
    }
}

final readonly class StubDirectoryManager implements DirectoryManager
{
    public function __construct(
        private bool $isWritable,
    ) {}

    public function ensureWritable(string $path): bool
    {
        return $this->isWritable;
    }
}
