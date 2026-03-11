<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Operations\Health;

use App\Infrastructure\Cache\ArrayCacheStore;
use App\Operations\Health\ReadinessChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Config\DatabaseConfig;
use Showoff\Core\Health\DirectoryManager;
use Showoff\Core\Health\RuntimeInspector;
use Showoff\Core\Health\SystemHealthChecker;

#[CoversClass(ReadinessChecker::class)]
final class ReadinessCheckerTest extends TestCase
{
    public function testItReturnsReadyWhenAllChecksPass(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $checker = new ReadinessChecker(
            new SystemHealthChecker(
                runtimeInspector: new ReadinessRuntimeInspectorStub('8.5.3'),
                directoryManager: new ReadinessDirectoryManagerStub(true),
            ),
            $this->config(),
            $pdo,
            new ArrayCacheStore(),
        );

        $report = $checker->check();

        self::assertSame('ready', $report['status']);
        self::assertTrue($report['checks']['runtime']);
        self::assertTrue($report['checks']['database']);
        self::assertTrue($report['checks']['cache']);
    }

    private function config(): AppConfig
    {
        return new AppConfig(
            appName: 'Showoff',
            cliName: 'showoff',
            environment: AppEnvironment::Test,
            debug: false,
            timezone: 'UTC',
            cacheDir: '/tmp',
            logLevel: 'info',
            secret: '0123456789abcdef0123456789abcdef',
            buildCommit: null,
            appUrl: 'http://localhost:8080',
            sessionName: 'SHOWOFFSESSID',
            sessionCookieSecure: false,
            database: new DatabaseConfig('sqlite', 'sqlite::memory:', null, null, null, null, null, 'utf8mb4'),
        );
    }
}

final readonly class ReadinessRuntimeInspectorStub implements RuntimeInspector
{
    public function __construct(
        private string $phpVersion,
    ) {}

    public function phpVersion(): string
    {
        return $this->phpVersion;
    }

    public function isExtensionLoaded(string $extension): bool
    {
        return true;
    }
}

final readonly class ReadinessDirectoryManagerStub implements DirectoryManager
{
    public function __construct(
        private bool $writable,
    ) {}

    public function ensureWritable(string $path): bool
    {
        return $this->writable;
    }
}
