<?php

declare(strict_types=1);

namespace Showoff\Core\Config;

final readonly class ConfigLoader
{
    public function __construct(
        private string $projectRoot,
    ) {}

    public function load(EnvironmentReader $environmentReader): AppConfig
    {
        $environment = AppEnvironment::fromString($environmentReader->string('APP_ENV', 'local') ?? 'local');
        $debug = $environmentReader->bool('APP_DEBUG', !$environment->isProduction());
        $timezone = $environmentReader->string('APP_TIMEZONE', 'UTC') ?? 'UTC';
        $cacheDir = $environmentReader->string('APP_CACHE_DIR', $this->projectRoot . '/var/cache') ?? $this->projectRoot . '/var/cache';
        $logLevel = strtolower($environmentReader->string('APP_LOG_LEVEL', 'info') ?? 'info');
        $secret = $environmentReader->string('APP_SECRET', $environment->isProduction() ? null : 'local-development-secret-key');
        $buildCommit = $environmentReader->string('APP_BUILD_COMMIT');
        $appUrl = $environmentReader->string('APP_URL', 'http://localhost:8080') ?? 'http://localhost:8080';
        $sessionName = $environmentReader->string('APP_SESSION_NAME', 'SHOWOFFSESSID') ?? 'SHOWOFFSESSID';
        $sessionCookieSecure = $environmentReader->bool('APP_SESSION_COOKIE_SECURE', $environment->isProduction());
        $databaseConfig = $this->buildDatabaseConfig($environmentReader);

        $this->assertTimezone($timezone);
        $this->assertLogLevel($logLevel);
        $this->assertSecret($secret, $environment);
        $this->assertBuildCommit($buildCommit);
        $this->assertAppUrl($appUrl);
        $this->assertSessionName($sessionName);

        return new AppConfig(
            appName: $environmentReader->string('APP_NAME', 'Showoff PHP Core') ?? 'Showoff PHP Core',
            cliName: $environmentReader->string('APP_CLI_NAME', 'showoff-core') ?? 'showoff-core',
            environment: $environment,
            debug: $debug,
            timezone: $timezone,
            cacheDir: $cacheDir,
            logLevel: $logLevel,
            secret: $secret ?? '',
            buildCommit: $buildCommit,
            appUrl: $appUrl,
            sessionName: $sessionName,
            sessionCookieSecure: $sessionCookieSecure,
            database: $databaseConfig,
        );
    }

    private function buildDatabaseConfig(EnvironmentReader $environmentReader): DatabaseConfig
    {
        $config = new DatabaseConfig(
            driver: strtolower($environmentReader->string('DATABASE_DRIVER', 'mysql') ?? 'mysql'),
            dsn: $environmentReader->string('DATABASE_DSN'),
            host: $environmentReader->string('DATABASE_HOST'),
            port: $environmentReader->int('DATABASE_PORT'),
            database: $environmentReader->string('DATABASE_NAME'),
            username: $environmentReader->string('DATABASE_USER'),
            password: $environmentReader->string('DATABASE_PASSWORD'),
            charset: $environmentReader->string('DATABASE_CHARSET', 'utf8mb4') ?? 'utf8mb4',
        );

        $this->assertDatabaseConfig($config);

        return $config;
    }

    private function assertTimezone(string $timezone): void
    {
        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            throw new ConfigurationException(sprintf('Unsupported APP_TIMEZONE "%s".', $timezone));
        }
    }

    private function assertLogLevel(string $logLevel): void
    {
        $allowed = ['debug', 'info', 'notice', 'warning', 'error', 'critical'];

        if (!in_array($logLevel, $allowed, true)) {
            throw new ConfigurationException(sprintf(
                'APP_LOG_LEVEL must be one of: %s.',
                implode(', ', $allowed),
            ));
        }
    }

    private function assertSecret(?string $secret, AppEnvironment $environment): void
    {
        if ($secret === null || trim($secret) === '') {
            throw new ConfigurationException('APP_SECRET must not be empty.');
        }

        if ($environment->isProduction() && strlen($secret) < 16) {
            throw new ConfigurationException('APP_SECRET must be at least 16 characters in production.');
        }
    }

    private function assertBuildCommit(?string $buildCommit): void
    {
        if ($buildCommit === null || $buildCommit === '') {
            return;
        }

        if (preg_match('/^[a-f0-9]{7,40}$/i', $buildCommit) !== 1) {
            throw new ConfigurationException('APP_BUILD_COMMIT must be a git SHA-like value.');
        }
    }

    private function assertAppUrl(string $appUrl): void
    {
        $isValid = filter_var($appUrl, FILTER_VALIDATE_URL) !== false;

        if (!$isValid) {
            throw new ConfigurationException('APP_URL must be a valid absolute URL.');
        }
    }

    private function assertSessionName(string $sessionName): void
    {
        if (preg_match('/^[A-Z0-9_]{3,32}$/', $sessionName) !== 1) {
            throw new ConfigurationException('APP_SESSION_NAME must match /^[A-Z0-9_]{3,32}$/.');
        }
    }

    private function assertDatabaseConfig(DatabaseConfig $databaseConfig): void
    {
        if ($databaseConfig->dsn !== null) {
            if (!str_contains($databaseConfig->dsn, ':')) {
                throw new ConfigurationException('DATABASE_DSN must be a valid PDO DSN.');
            }

            return;
        }

        if ($databaseConfig->driver !== 'mysql') {
            throw new ConfigurationException('DATABASE_DRIVER must be "mysql" when DATABASE_DSN is not set.');
        }

        if ($databaseConfig->host === null || $databaseConfig->database === null) {
            throw new ConfigurationException('DATABASE_HOST and DATABASE_NAME are required.');
        }

        if ($databaseConfig->username === null || $databaseConfig->password === null) {
            throw new ConfigurationException('DATABASE_USER and DATABASE_PASSWORD are required.');
        }

        if ($databaseConfig->port === null || $databaseConfig->port < 1 || $databaseConfig->port > 65535) {
            throw new ConfigurationException('DATABASE_PORT must be between 1 and 65535.');
        }

        if ($databaseConfig->charset === null || trim($databaseConfig->charset) === '') {
            throw new ConfigurationException('DATABASE_CHARSET must not be empty.');
        }
    }
}
