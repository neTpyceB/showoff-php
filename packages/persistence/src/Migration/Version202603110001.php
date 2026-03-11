<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Migration;

use PDO;

final readonly class Version202603110001 implements Migration
{
    public function version(): string
    {
        return '202603110001';
    }

    public function description(): string
    {
        return 'Add performance indexes for contact submissions.';
    }

    public function up(PDO $connection): void
    {
        if ($this->driver($connection) === 'sqlite') {
            $connection->exec(
                'CREATE INDEX IF NOT EXISTS idx_contact_submissions_submitted_at ON contact_submissions (submitted_at)',
            );
            $connection->exec(
                'CREATE INDEX IF NOT EXISTS idx_contact_submissions_status_submitted_at ON contact_submissions (status, submitted_at)',
            );

            return;
        }

        $this->createMySqlIndexIfMissing(
            $connection,
            'idx_contact_submissions_submitted_at',
            'CREATE INDEX idx_contact_submissions_submitted_at ON contact_submissions (submitted_at)',
        );
        $this->createMySqlIndexIfMissing(
            $connection,
            'idx_contact_submissions_status_submitted_at',
            'CREATE INDEX idx_contact_submissions_status_submitted_at ON contact_submissions (status, submitted_at)',
        );
    }

    private function createMySqlIndexIfMissing(PDO $connection, string $indexName, string $sql): void
    {
        $statement = $connection->prepare(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = :table_name AND index_name = :index_name LIMIT 1',
        );
        $statement->execute([
            'table_name' => 'contact_submissions',
            'index_name' => $indexName,
        ]);

        if ($statement->fetchColumn() !== false) {
            return;
        }

        $connection->exec($sql);
    }

    private function driver(PDO $connection): string
    {
        $driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);

        return is_string($driver) ? $driver : 'unknown';
    }
}
