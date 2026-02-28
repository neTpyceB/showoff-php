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

        $this->assertTimezone($timezone);
        $this->assertLogLevel($logLevel);
        $this->assertSecret($secret, $environment);
        $this->assertBuildCommit($buildCommit);

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
}
