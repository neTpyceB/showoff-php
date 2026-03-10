<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Migration;

use PDO;

final readonly class Version202603100001 implements Migration
{
    public function version(): string
    {
        return '202603100001';
    }

    public function description(): string
    {
        return 'Create users and API access tokens tables.';
    }

    public function up(PDO $connection): void
    {
        if ($this->driver($connection) === 'sqlite') {
            $connection->exec(
                'CREATE TABLE IF NOT EXISTS app_users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    email_hash VARCHAR(64) NOT NULL UNIQUE,
                    email_encrypted TEXT NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    role VARCHAR(32) NOT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL
                )',
            );
            $connection->exec(
                'CREATE TABLE IF NOT EXISTS api_access_tokens (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    token_hash VARCHAR(64) NOT NULL UNIQUE,
                    token_name VARCHAR(80) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    revoked_at DATETIME NULL,
                    last_used_at DATETIME NULL,
                    created_at DATETIME NOT NULL,
                    CONSTRAINT fk_api_access_tokens_user FOREIGN KEY (user_id)
                        REFERENCES app_users(id)
                        ON DELETE CASCADE
                )',
            );
            $connection->exec(
                'CREATE INDEX IF NOT EXISTS idx_api_access_tokens_user_id ON api_access_tokens (user_id)',
            );

            return;
        }

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS app_users (
                id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
                email_hash CHAR(64) NOT NULL UNIQUE,
                email_encrypted TEXT NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(32) NOT NULL,
                created_at DATETIME(6) NOT NULL,
                updated_at DATETIME(6) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        );
        $connection->exec(
            'CREATE TABLE IF NOT EXISTS api_access_tokens (
                id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                token_hash CHAR(64) NOT NULL UNIQUE,
                token_name VARCHAR(80) NOT NULL,
                expires_at DATETIME(6) NOT NULL,
                revoked_at DATETIME(6) NULL,
                last_used_at DATETIME(6) NULL,
                created_at DATETIME(6) NOT NULL,
                INDEX idx_api_access_tokens_user_id (user_id),
                CONSTRAINT fk_api_access_tokens_user FOREIGN KEY (user_id)
                    REFERENCES app_users(id)
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
