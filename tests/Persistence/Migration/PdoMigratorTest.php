<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Persistence\Migration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Persistence\Migration\PdoMigrator;
use Showoff\Core\Persistence\Migration\Version202603020001;
use Showoff\Core\Persistence\Migration\Version202603100001;
use Showoff\Core\Persistence\Migration\Version202603110001;

#[CoversClass(PdoMigrator::class)]
#[CoversClass(Version202603020001::class)]
#[CoversClass(Version202603100001::class)]
#[CoversClass(Version202603110001::class)]
final class PdoMigratorTest extends TestCase
{
    public function testItReportsAndExecutesPendingMigrations(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $migrator = new PdoMigrator($pdo, [new Version202603020001(), new Version202603100001(), new Version202603110001()]);

        $statusBefore = $migrator->status();
        $result = $migrator->migrate();
        $statusAfter = $migrator->status();

        self::assertSame(['202603020001', '202603100001', '202603110001'], $statusBefore->pendingVersions);
        self::assertSame(['202603020001', '202603100001', '202603110001'], $result->appliedVersions);
        self::assertSame([], $statusAfter->pendingVersions);
        self::assertSame(['202603020001', '202603100001', '202603110001'], $statusAfter->appliedVersions);
    }
}
