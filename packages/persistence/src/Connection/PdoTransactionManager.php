<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Connection;

use PDO;
use Showoff\Core\Domain\Shared\TransactionBoundary;
use Throwable;

final readonly class PdoTransactionManager implements TransactionBoundary
{
    public function __construct(
        private PDO $connection,
    ) {}

    public function transactional(callable $operation): mixed
    {
        if ($this->connection->inTransaction()) {
            return $operation();
        }

        $this->connection->beginTransaction();

        try {
            $result = $operation();
            $this->connection->commit();

            return $result;
        } catch (Throwable $throwable) {
            $this->connection->rollBack();

            throw $throwable;
        }
    }
}
