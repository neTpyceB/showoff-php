<?php

declare(strict_types=1);

namespace App\Messaging;

use App\Cache\CacheStore;
use App\Messaging\Message\ContactSubmissionStoredMessage;

final readonly class ContactSubmissionEventHandler
{
    public function __construct(
        private CacheStore $cache,
    ) {}

    public function handle(ContactSubmissionStoredMessage $message): void
    {
        $this->cache->increment('analytics:contact_submissions:processed');
        $this->cache->set('analytics:contact_submissions:last_email', $message->email, 3600);
    }
}
