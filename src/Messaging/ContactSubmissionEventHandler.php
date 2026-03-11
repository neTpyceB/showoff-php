<?php

declare(strict_types=1);

namespace App\Messaging;

use App\Cache\CacheStore;
use App\Messaging\Message\ContactSubmissionStoredMessage;
use App\Realtime\Publisher\RealtimePublisher;

final readonly class ContactSubmissionEventHandler
{
    public function __construct(
        private CacheStore $cache,
        private RealtimePublisher $realtimePublisher,
        private string $contactTopic,
    ) {}

    public function handle(ContactSubmissionStoredMessage $message): void
    {
        $this->cache->increment('analytics:contact_submissions:processed');
        $this->cache->set('analytics:contact_submissions:last_email', $message->email, 3600);
        $this->cache->set('analytics:contact_submissions:last_occurred_at', $message->occurredAt, 3600);

        $this->realtimePublisher->publish($this->contactTopic, [
            'eventType' => ContactSubmissionStoredMessage::EVENT_TYPE,
            'eventVersion' => ContactSubmissionStoredMessage::EVENT_VERSION,
            'submissionId' => $message->submissionId,
            'email' => $message->email,
            'source' => $message->source,
            'occurredAt' => $message->occurredAt,
        ]);
    }
}
