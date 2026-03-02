<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Connection;

interface TransactionManager
{
    /**
     * @template T
     *
     * @param callable(): T $operation
     *
     * @return T
     */
    public function transactional(callable $operation): mixed;
}
