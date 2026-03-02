<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Console\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Console\Command\DatabaseStatusCommand;
use Showoff\Core\Persistence\Migration\PdoMigrator;
use Showoff\Core\Persistence\Migration\Version202603020001;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(DatabaseStatusCommand::class)]
final class DatabaseStatusCommandTest extends TestCase
{
    public function testItPrintsPendingMigrationStatus(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $command = new DatabaseStatusCommand(new PdoMigrator($pdo, [new Version202603020001()]));

        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([]));
        self::assertStringContainsString('Pending migrations', $tester->getDisplay());
        self::assertStringContainsString('202603020001', $tester->getDisplay());
    }
}
