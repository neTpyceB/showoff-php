<?php

declare(strict_types=1);

namespace Showoff\Core\Config;

final readonly class DatabaseConfig
{
    public function __construct(
        public string $driver,
        public ?string $dsn,
        public ?string $host,
        public ?int $port,
        public ?string $database,
        public ?string $username,
        public ?string $password,
        public ?string $charset,
    ) {}

    public function dsn(): string
    {
        if ($this->dsn !== null) {
            return $this->dsn;
        }

        return match ($this->driver) {
            'mysql' => sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->host,
                $this->port,
                $this->database,
                $this->charset,
            ),
            default => throw new ConfigurationException(sprintf(
                'Unsupported database driver "%s".',
                $this->driver,
            )),
        };
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'driver' => $this->driver,
            'dsn' => $this->dsn,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
            'charset' => $this->charset,
        ];
    }
}
