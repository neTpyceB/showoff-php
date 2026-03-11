<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Realtime\Publisher;

use App\Realtime\Publisher\MercureRealtimePublisher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MercureRealtimePublisher::class)]
final class MercureRealtimePublisherTest extends TestCase
{
    public function testItSkipsPublishingWhenHubUrlIsEmpty(): void
    {
        $publisher = new MercureRealtimePublisher('', '', 10);
        $publisher->publish('/realtime/contact-submissions', ['event' => 'contact.submission.stored']);

        self::addToAssertionCount(1);
    }

    public function testItIgnoresJsonEncodingFailures(): void
    {
        $publisher = new MercureRealtimePublisher('http://127.0.0.1:9/.well-known/mercure', '', 1);
        $stream = fopen('php://memory', 'rb');
        if (!is_resource($stream)) {
            self::fail('Cannot allocate stream resource for test.');
        }

        try {
            $publisher->publish('/realtime/contact-submissions', ['resource' => $stream]);
        } finally {
            fclose($stream);
        }

        self::addToAssertionCount(1);
    }
}
