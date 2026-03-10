<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Migration;

use PDO;

final readonly class PdoMigratorFactory
{
    public function __construct(
        private PDO $connection,
    ) {}

    public function create(): PdoMigrator
    {
        return new PdoMigrator($this->connection, [
            new Version202603020001(),
            new Version202603100001(),
        ]);
    }
}
