<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Console\Command;

use App\Security\Crypto\Encryptor;
use App\Security\PasswordHasher;
use App\Security\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Config\DatabaseConfig;
use Showoff\Core\Console\Command\SecurityCreateUserCommand;
use Showoff\Core\Persistence\Migration\PdoMigrator;
use Showoff\Core\Persistence\Migration\Version202603020001;
use Showoff\Core\Persistence\Migration\Version202603100001;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(SecurityCreateUserCommand::class)]
final class SecurityCreateUserCommandTest extends TestCase
{
    public function testItCreatesNewUser(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $migrator = new PdoMigrator($pdo, [new Version202603020001(), new Version202603100001()]);
        $migrator->migrate();

        $repository = new UserRepository($pdo, new Encryptor($this->appConfig()));
        $command = new SecurityCreateUserCommand($repository, new PasswordHasher());
        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([
            'email' => 'admin@example.com',
            'password' => 'VeryStrongPassword123!',
            'role' => 'admin',
        ]));

        self::assertStringContainsString('Created user', $tester->getDisplay());
        self::assertNotNull($repository->findByEmail('admin@example.com'));
    }

    private function appConfig(): AppConfig
    {
        return new AppConfig(
            appName: 'Showoff PHP Core',
            cliName: 'showoff-core',
            environment: AppEnvironment::Test,
            debug: true,
            timezone: 'UTC',
            cacheDir: '/tmp',
            logLevel: 'debug',
            secret: 'test-secret-key-with-minimum-length',
            buildCommit: null,
            appUrl: 'http://localhost',
            sessionName: 'SHOWOFFSESSID',
            sessionCookieSecure: false,
            database: new DatabaseConfig(
                driver: 'sqlite',
                dsn: 'sqlite::memory:',
                host: null,
                port: null,
                database: null,
                username: null,
                password: null,
                charset: null,
            ),
        );
    }
}
