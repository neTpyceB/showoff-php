<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Bootstrap\Factory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Showoff\Core\Bootstrap\Factory\ConsoleApplicationFactory;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Config\DatabaseConfig;
use Showoff\Core\Console\Command\AboutCommand;
use Showoff\Core\Console\Command\ConfigDumpCommand;
use Showoff\Core\Console\Command\DatabaseMigrateCommand;
use Showoff\Core\Console\Command\DatabaseStatusCommand;
use Showoff\Core\Console\Command\HealthCheckCommand;
use Symfony\Component\Console\Command\Command;

#[CoversClass(ConsoleApplicationFactory::class)]
final class ConsoleApplicationFactoryTest extends TestCase
{
    public function testItBuildsConsoleApplicationWithCommandLoader(): void
    {
        $container = new FactoryContainer([
            AboutCommand::class => new DummyCommand('app:about'),
            ConfigDumpCommand::class => new DummyCommand('app:config:dump'),
            DatabaseMigrateCommand::class => new DummyCommand('app:database:migrate'),
            DatabaseStatusCommand::class => new DummyCommand('app:database:status'),
            HealthCheckCommand::class => new DummyCommand('app:health:check'),
        ]);
        $factory = new ConsoleApplicationFactory($this->config(), $container);
        $application = $factory->create();

        self::assertSame('Core App', $application->getName());
        self::assertSame('app:about', $application->find('app:about')->getName());
        self::assertSame('app:database:migrate', $application->find('app:database:migrate')->getName());
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
            appUrl: 'http://localhost:8081',
            sessionName: 'SHOWOFFSESSID',
            sessionCookieSecure: false,
            database: new DatabaseConfig('mysql', null, 'db', 3306, 'showoff', 'showoff', 'showoff', 'utf8mb4'),
        );
    }
}

final class DummyCommand extends Command
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output,
    ): int {
        return Command::SUCCESS;
    }
}

final readonly class FactoryContainer implements ContainerInterface
{
    /**
     * @param array<string, object> $services
     */
    public function __construct(
        private array $services,
    ) {}

    public function get(string $id): mixed
    {
        return $this->services[$id] ?? throw new class (sprintf('Missing service: %s', $id)) extends \RuntimeException implements \Psr\Container\NotFoundExceptionInterface {};
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}
