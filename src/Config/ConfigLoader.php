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
        );
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
}
