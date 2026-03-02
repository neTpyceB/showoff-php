<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Connection;

use PDO;
use PDOException;
use Showoff\Core\Config\DatabaseConfig;

final class PdoConnectionFactory
{
    public function create(DatabaseConfig $config): PDO
    {
        try {
            $connection = new PDO(
                $config->dsn(),
                $config->username,
                $config->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
        } catch (PDOException $exception) {
            throw new ConnectionException(
                sprintf('Unable to establish database connection: %s', $exception->getMessage()),
                (int) $exception->getCode(),
                $exception,
            );
        }

        if ($config->driver === 'sqlite' || str_starts_with($config->dsn(), 'sqlite:')) {
            $connection->exec('PRAGMA foreign_keys = ON');
        }

        return $connection;
    }
}
