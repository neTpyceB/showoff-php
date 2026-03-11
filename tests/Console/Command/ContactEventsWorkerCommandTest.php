<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Console\Command;

use App\Messaging\ContactSubmissionConsumer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Console\Command\ContactEventsWorkerCommand;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(ContactEventsWorkerCommand::class)]
final class ContactEventsWorkerCommandTest extends TestCase
{
    public function testItConsumesConfiguredMessageLimit(): void
    {
        $consumer = new FakeContactSubmissionConsumer();
        $command = new ContactEventsWorkerCommand($consumer);
        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute(['--limit' => '3']));
        self::assertSame(3, $consumer->lastLimit);
        self::assertStringContainsString('Processed 2 queued event(s).', $tester->getDisplay());
    }
}

final class FakeContactSubmissionConsumer implements ContactSubmissionConsumer
{
    public int $lastLimit = 0;

    public function consume(int $limit): int
    {
        $this->lastLimit = $limit;

        return 2;
    }
}
