<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Messaging\Message;

use App\Messaging\Message\ContactSubmissionStoredMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactSubmissionStoredMessage::class)]
final class ContactSubmissionStoredMessageTest extends TestCase
{
    public function testItSerializesWithEventMetadataAndParsesBack(): void
    {
        $message = new ContactSubmissionStoredMessage(
            submissionId: 51,
            email: 'event@example.com',
            source: 'rest_api',
            occurredAt: '2026-03-11T10:00:00+00:00',
        );

        $json = $message->toJson();
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        self::assertIsArray($decoded);
        self::assertSame(ContactSubmissionStoredMessage::EVENT_TYPE, $decoded['eventType'] ?? null);
        self::assertSame(ContactSubmissionStoredMessage::EVENT_VERSION, $decoded['eventVersion'] ?? null);

        $parsed = ContactSubmissionStoredMessage::fromJson($json);
        self::assertSame(51, $parsed->submissionId);
        self::assertSame('event@example.com', $parsed->email);
        self::assertSame('rest_api', $parsed->source);
        self::assertSame('2026-03-11T10:00:00+00:00', $parsed->occurredAt);
    }
}
