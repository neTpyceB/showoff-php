<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Persistence\Connection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Persistence\Connection\PdoTransactionManager;

#[CoversClass(PdoTransactionManager::class)]
final class PdoTransactionManagerTest extends TestCase
{
    public function testItCommitsSuccessfulTransactions(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE entries (value TEXT NOT NULL)');

        $manager = new PdoTransactionManager($pdo);
        $manager->transactional(static function () use ($pdo): void {
            $pdo->exec("INSERT INTO entries (value) VALUES ('ok')");
        });

        $count = $pdo->query('SELECT COUNT(*) FROM entries');

        self::assertNotFalse($count);
        self::assertSame('1', (string) $count->fetchColumn());
    }

    public function testItRollsBackFailedTransactions(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE entries (value TEXT NOT NULL)');

        $manager = new PdoTransactionManager($pdo);
        $exception = null;

        try {
            $manager->transactional(static function () use ($pdo): void {
                $pdo->exec("INSERT INTO entries (value) VALUES ('ok')");

                throw new \RuntimeException('boom');
            });
        } catch (\RuntimeException $runtimeException) {
            $exception = $runtimeException;
        }

        $count = $pdo->query('SELECT COUNT(*) FROM entries');

        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertNotFalse($count);
        self::assertSame('0', (string) $count->fetchColumn());
    }
}
