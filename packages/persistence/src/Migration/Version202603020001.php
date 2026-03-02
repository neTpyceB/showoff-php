<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Migration;

use PDO;

final readonly class Version202603020001 implements Migration
{
    public function version(): string
    {
        return '202603020001';
    }

    public function description(): string
    {
        return 'Create contact submission and event tables.';
    }

    public function up(PDO $connection): void
    {
        if ($this->driver($connection) === 'sqlite') {
            $connection->exec(
                'CREATE TABLE IF NOT EXISTS contact_submissions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(120) NOT NULL,
                    email VARCHAR(180) NOT NULL,
                    message TEXT NOT NULL,
                    status VARCHAR(32) NOT NULL,
                    submitted_at DATETIME NOT NULL
                )',
            );
            $connection->exec(
                'CREATE TABLE IF NOT EXISTS contact_submission_events (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    submission_id INTEGER NOT NULL,
                    event_name VARCHAR(64) NOT NULL,
                    occurred_at DATETIME NOT NULL,
                    metadata_json TEXT NOT NULL,
                    CONSTRAINT fk_contact_submission_events_submission FOREIGN KEY (submission_id)
                        REFERENCES contact_submissions(id)
                        ON DELETE CASCADE
                )',
            );
            $connection->exec(
                'CREATE INDEX IF NOT EXISTS idx_contact_submission_events_submission_id ON contact_submission_events (submission_id)',
            );

            return;
        }

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS contact_submissions (
                id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(180) NOT NULL,
                message TEXT NOT NULL,
                status VARCHAR(32) NOT NULL,
                submitted_at DATETIME(6) NOT NULL,
                INDEX idx_contact_submissions_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        );
        $connection->exec(
            'CREATE TABLE IF NOT EXISTS contact_submission_events (
                id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
                submission_id BIGINT UNSIGNED NOT NULL,
                event_name VARCHAR(64) NOT NULL,
                occurred_at DATETIME(6) NOT NULL,
                metadata_json JSON NOT NULL,
                INDEX idx_contact_submission_events_submission_id (submission_id),
                CONSTRAINT fk_contact_submission_events_submission FOREIGN KEY (submission_id)
                    REFERENCES contact_submissions(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        );
    }

    private function driver(PDO $connection): string
    {
        $driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);

        return is_string($driver) ? $driver : 'unknown';
    }
}
