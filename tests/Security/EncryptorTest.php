<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Security;

use App\Security\Crypto\Encryptor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Config\DatabaseConfig;

#[CoversClass(Encryptor::class)]
final class EncryptorTest extends TestCase
{
    public function testItEncryptsAndDecryptsPayload(): void
    {
        $encryptor = new Encryptor($this->appConfig());
        $ciphertext = $encryptor->encrypt('sensitive@example.com');

        self::assertNotSame('sensitive@example.com', $ciphertext);
        self::assertSame('sensitive@example.com', $encryptor->decrypt($ciphertext));
    }

    public function testItRejectsInvalidCiphertext(): void
    {
        $encryptor = new Encryptor($this->appConfig());

        $this->expectException(\RuntimeException::class);
        $encryptor->decrypt('invalid');
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
