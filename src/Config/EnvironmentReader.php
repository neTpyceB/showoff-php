<?php

declare(strict_types=1);

namespace Showoff\Core\Config;

final readonly class EnvironmentReader
{
    /**
     * @param array<string, mixed> $server
     * @param array<string, mixed> $env
     */
    public function __construct(
        private array $server,
        private array $env,
    ) {}

    public static function fromGlobals(): self
    {
        /** @var array<string, mixed> $server */
        $server = $_SERVER;
        /** @var array<string, mixed> $env */
        $env = $_ENV;

        return new self($server, $env);
    }

    public function string(string $key, ?string $default = null): ?string
    {
        $value = $this->raw($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public function bool(string $key, bool $default): bool
    {
        $value = $this->string($key);

        if ($value === null) {
            return $default;
        }

        return match (strtolower($value)) {
            '1', 'true', 'on', 'yes' => true,
            '0', 'false', 'off', 'no' => false,
            default => throw new ConfigurationException(sprintf(
                'Environment variable "%s" must be a boolean-compatible value.',
                $key,
            )),
        };
    }

    private function raw(string $key): mixed
    {
        return $this->env[$key] ?? $this->server[$key] ?? getenv($key) ?: null;
    }
}
