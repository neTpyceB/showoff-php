<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Messaging;

use App\Infrastructure\Cache\ArrayCacheStore;
use App\Messaging\ContactSubmissionEventHandler;
use App\Messaging\Message\ContactSubmissionStoredMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactSubmissionEventHandler::class)]
final class ContactSubmissionEventHandlerTest extends TestCase
{
    public function testItUpdatesAnalyticsCacheKeys(): void
    {
        $cache = new ArrayCacheStore();
        $handler = new ContactSubmissionEventHandler($cache);

        $handler->handle(new ContactSubmissionStoredMessage(
            submissionId: 12,
            email: 'queue@example.com',
            source: 'rest_api',
            occurredAt: '2026-03-10T12:00:00+00:00',
        ));

        self::assertSame('1', $cache->get('analytics:contact_submissions:processed'));
        self::assertSame('queue@example.com', $cache->get('analytics:contact_submissions:last_email'));
    }
}
