<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Migration;

use PDO;

final readonly class PdoMigrator
{
    /**
     * @param list<Migration> $migrations
     */
    public function __construct(
        private PDO $connection,
        private array $migrations,
    ) {}

    public function status(): MigrationStatus
    {
        $this->ensureMetadataTable();

        $appliedVersions = $this->appliedVersions();
        $pendingVersions = [];

        foreach ($this->migrations as $migration) {
            if (!in_array($migration->version(), $appliedVersions, true)) {
                $pendingVersions[] = $migration->version();
            }
        }

        return new MigrationStatus($appliedVersions, $pendingVersions);
    }

    public function migrate(): MigrationExecutionResult
    {
        $this->ensureMetadataTable();

        $appliedVersions = $this->appliedVersions();
        $executed = [];
        $skipped = [];

        foreach ($this->migrations as $migration) {
            if (in_array($migration->version(), $appliedVersions, true)) {
                $skipped[] = $migration->version();

                continue;
            }

            $migration->up($this->connection);
            $statement = $this->connection->prepare(
                'INSERT INTO migration_versions (version, executed_at) VALUES (:version, :executed_at)',
            );
            $statement->execute([
                'version' => $migration->version(),
                'executed_at' => new \DateTimeImmutable()->format('Y-m-d H:i:s.u'),
            ]);

            $executed[] = $migration->version();
        }

        return new MigrationExecutionResult($executed, $skipped);
    }

    /**
     * @return list<string>
     */
    private function appliedVersions(): array
    {
        $result = $this->connection->query(
            'SELECT version FROM migration_versions ORDER BY version ASC',
        );
        if ($result === false) {
            throw new \RuntimeException('Unable to load applied migration versions.');
        }

        /** @var list<string> $versions */
        $versions = $result->fetchAll(PDO::FETCH_COLUMN);

        return $versions;
    }

    private function ensureMetadataTable(): void
    {
        if ($this->driver() === 'sqlite') {
            $this->connection->exec(
                'CREATE TABLE IF NOT EXISTS migration_versions (version VARCHAR(191) PRIMARY KEY, executed_at DATETIME NOT NULL)',
            );

            return;
        }

        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS migration_versions (version VARCHAR(191) NOT NULL PRIMARY KEY, executed_at DATETIME(6) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        );
    }

    private function driver(): string
    {
        $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

        return is_string($driver) ? $driver : 'unknown';
    }
}
