<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Messaging;

use App\Infrastructure\Cache\ArrayCacheStore;
use App\Messaging\ContactSubmissionEventHandler;
use App\Messaging\Message\ContactSubmissionStoredMessage;
use App\Realtime\Publisher\RealtimePublisher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactSubmissionEventHandler::class)]
final class ContactSubmissionEventHandlerTest extends TestCase
{
    public function testItUpdatesAnalyticsCacheKeys(): void
    {
        $cache = new ArrayCacheStore();
        $realtime = new InMemoryRealtimePublisher();
        $handler = new ContactSubmissionEventHandler($cache, $realtime, '/realtime/contact-submissions');

        $handler->handle(new ContactSubmissionStoredMessage(
            submissionId: 12,
            email: 'queue@example.com',
            source: 'rest_api',
            occurredAt: '2026-03-10T12:00:00+00:00',
        ));

        self::assertSame('1', $cache->get('analytics:contact_submissions:processed'));
        self::assertSame('queue@example.com', $cache->get('analytics:contact_submissions:last_email'));
        self::assertSame('2026-03-10T12:00:00+00:00', $cache->get('analytics:contact_submissions:last_occurred_at'));
        self::assertCount(1, $realtime->published);
        self::assertSame('/realtime/contact-submissions', $realtime->published[0]['topic']);
        self::assertSame(12, $realtime->published[0]['payload']['submissionId'] ?? null);
    }
}

final class InMemoryRealtimePublisher implements RealtimePublisher
{
    /** @var list<array{topic: string, payload: array<string, mixed>}> */
    public array $published = [];

    public function publish(string $topic, array $payload): void
    {
        $this->published[] = [
            'topic' => $topic,
            'payload' => $payload,
        ];
    }
}
