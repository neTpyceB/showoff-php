<?php

declare(strict_types=1);

namespace App\Module\Analytics\Application;

use App\Cache\CacheStore;
use App\Module\Analytics\Api\AnalyticsPublicApi;
use App\Module\Analytics\Api\ContactSubmissionProcessingView;

final readonly class CachedAnalyticsPublicApi implements AnalyticsPublicApi
{
    private const KEY_PROCESSED = 'analytics:contact_submissions:processed';
    private const KEY_LAST_EMAIL = 'analytics:contact_submissions:last_email';
    private const KEY_LAST_OCCURRED_AT = 'analytics:contact_submissions:last_occurred_at';

    public function __construct(
        private CacheStore $cache,
    ) {}

    public function contactSubmissionProcessing(): ContactSubmissionProcessingView
    {
        return new ContactSubmissionProcessingView(
            processed: $this->intValue($this->cache->get(self::KEY_PROCESSED)),
            lastEmail: $this->stringValueOrNull($this->cache->get(self::KEY_LAST_EMAIL)),
            lastOccurredAt: $this->stringValueOrNull($this->cache->get(self::KEY_LAST_OCCURRED_AT)),
        );
    }

    private function intValue(mixed $value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return max(0, (int) $value);
    }

    private function stringValueOrNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
