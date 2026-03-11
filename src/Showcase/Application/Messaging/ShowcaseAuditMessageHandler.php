<?php

declare(strict_types=1);

namespace App\Showcase\Application\Messaging;

use App\Cache\CacheStore;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ShowcaseAuditMessageHandler
{
    public function __construct(
        private CacheStore $cache,
    ) {}

    public function __invoke(ShowcaseAuditMessage $message): void
    {
        $this->cache->increment('showcase:audit:messages_total');
        $this->cache->set('showcase:audit:last_action', $message->action, 3600);
        $this->cache->set('showcase:audit:last_occurred_at', $message->occurredAt, 3600);
    }
}
