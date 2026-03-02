<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Config\ConfigLoader;
use Showoff\Core\Config\ConfigurationException;
use Showoff\Core\Config\DatabaseConfig;
use Showoff\Core\Config\EnvironmentReader;

#[CoversClass(ConfigLoader::class)]
final class ConfigLoaderTest extends TestCase
{
    public function testItBuildsApplicationConfigurationFromEnvironment(): void
    {
        $loader = new ConfigLoader('/app');
        $config = $loader->load(new EnvironmentReader(
            server: [],
            env: [
                'APP_NAME' => 'Portfolio Core',
                'APP_CLI_NAME' => 'portfolio-core',
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'APP_TIMEZONE' => 'Europe/Paris',
                'APP_CACHE_DIR' => '/tmp/cache',
                'APP_LOG_LEVEL' => 'warning',
                'APP_SECRET' => '0123456789abcdef',
                'APP_BUILD_COMMIT' => 'a1b2c3d4',
                'APP_URL' => 'https://portfolio-core.test',
                'APP_SESSION_NAME' => 'PORTFOLIOSESSID',
                'APP_SESSION_COOKIE_SECURE' => 'true',
                'DATABASE_HOST' => 'db',
                'DATABASE_PORT' => '3306',
                'DATABASE_NAME' => 'showoff',
                'DATABASE_USER' => 'showoff',
                'DATABASE_PASSWORD' => 'topsecret',
                'DATABASE_CHARSET' => 'utf8mb4',
            ],
        ));

        self::assertSame('Portfolio Core', $config->appName);
        self::assertSame('portfolio-core', $config->cliName);
        self::assertSame(AppEnvironment::Production, $config->environment);
        self::assertFalse($config->debug);
        self::assertSame('Europe/Paris', $config->timezone);
        self::assertSame('/tmp/cache', $config->cacheDir);
        self::assertSame('warning', $config->logLevel);
        self::assertSame('0123456789abcdef', $config->secret);
        self::assertSame('a1b2c3d4', $config->buildCommit);
        self::assertSame('https://portfolio-core.test', $config->appUrl);
        self::assertSame('PORTFOLIOSESSID', $config->sessionName);
        self::assertTrue($config->sessionCookieSecure);
        self::assertEquals(new DatabaseConfig(
            driver: 'mysql',
            dsn: null,
            host: 'db',
            port: 3306,
            database: 'showoff',
            username: 'showoff',
            password: 'topsecret',
            charset: 'utf8mb4',
        ), $config->database);
    }

    public function testItRejectsInvalidTimezones(): void
    {
        $loader = new ConfigLoader('/app');

        $this->expectException(ConfigurationException::class);
        $loader->load(new EnvironmentReader(
            server: [],
            env: [
                'APP_TIMEZONE' => 'Mars/Olympus',
                'APP_SECRET' => 'local-development-secret-key',
                'DATABASE_HOST' => 'db',
                'DATABASE_PORT' => '3306',
                'DATABASE_NAME' => 'showoff',
                'DATABASE_USER' => 'showoff',
                'DATABASE_PASSWORD' => 'showoff',
            ],
        ));
    }

    public function testItRequiresAStrongSecretInProduction(): void
    {
        $loader = new ConfigLoader('/app');

        $this->expectException(ConfigurationException::class);
        $loader->load(new EnvironmentReader(
            server: [],
            env: [
                'APP_ENV' => 'production',
                'APP_SECRET' => 'short',
                'DATABASE_HOST' => 'db',
                'DATABASE_PORT' => '3306',
                'DATABASE_NAME' => 'showoff',
                'DATABASE_USER' => 'showoff',
                'DATABASE_PASSWORD' => 'showoff',
            ],
        ));
    }

    public function testItRejectsInvalidAppUrl(): void
    {
        $loader = new ConfigLoader('/app');

        $this->expectException(ConfigurationException::class);
        $loader->load(new EnvironmentReader(
            server: [],
            env: [
                'APP_SECRET' => 'local-development-secret-key',
                'APP_URL' => 'not-a-url',
                'DATABASE_HOST' => 'db',
                'DATABASE_PORT' => '3306',
                'DATABASE_NAME' => 'showoff',
                'DATABASE_USER' => 'showoff',
                'DATABASE_PASSWORD' => 'showoff',
            ],
        ));
    }

    public function testItRejectsInvalidDatabasePort(): void
    {
        $loader = new ConfigLoader('/app');

        $this->expectException(ConfigurationException::class);
        $loader->load(new EnvironmentReader(
            server: [],
            env: [
                'APP_SECRET' => 'local-development-secret-key',
                'DATABASE_HOST' => 'db',
                'DATABASE_PORT' => '70000',
                'DATABASE_NAME' => 'showoff',
                'DATABASE_USER' => 'showoff',
                'DATABASE_PASSWORD' => 'showoff',
            ],
        ));
    }
}
