<?php

declare(strict_types=1);

namespace Showoff\Core\Config;

final readonly class AppConfig
{
    public function __construct(
        public string $appName,
        public string $cliName,
        public AppEnvironment $environment,
        public bool $debug,
        public string $timezone,
        public string $cacheDir,
        public string $logLevel,
        public string $secret,
        public ?string $buildCommit,
        public string $appUrl,
        public string $sessionName,
        public bool $sessionCookieSecure,
        public DatabaseConfig $database,
    ) {}

    /**
     * @return array<string, array<string, int|string|null>|bool|string|null>
     */
    public function toArray(): array
    {
        return [
            'app_name' => $this->appName,
            'cli_name' => $this->cliName,
            'environment' => $this->environment->value,
            'debug' => $this->debug,
            'timezone' => $this->timezone,
            'cache_dir' => $this->cacheDir,
            'log_level' => $this->logLevel,
            'secret' => $this->secret,
            'build_commit' => $this->buildCommit,
            'app_url' => $this->appUrl,
            'session_name' => $this->sessionName,
            'session_cookie_secure' => $this->sessionCookieSecure,
            'database' => $this->database->toArray(),
        ];
    }
}
