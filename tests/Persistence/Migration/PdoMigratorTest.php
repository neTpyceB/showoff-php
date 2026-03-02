<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Persistence\Migration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Persistence\Migration\PdoMigrator;
use Showoff\Core\Persistence\Migration\Version202603020001;

#[CoversClass(PdoMigrator::class)]
#[CoversClass(Version202603020001::class)]
final class PdoMigratorTest extends TestCase
{
    public function testItReportsAndExecutesPendingMigrations(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $migrator = new PdoMigrator($pdo, [new Version202603020001()]);

        $statusBefore = $migrator->status();
        $result = $migrator->migrate();
        $statusAfter = $migrator->status();

        self::assertSame(['202603020001'], $statusBefore->pendingVersions);
        self::assertSame(['202603020001'], $result->appliedVersions);
        self::assertSame([], $statusAfter->pendingVersions);
        self::assertSame(['202603020001'], $statusAfter->appliedVersions);
    }
}
