<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Security\RateLimit;

use App\Infrastructure\Cache\ArrayCacheStore;
use App\Security\RateLimit\FailedAuthRateLimiter;
use App\Security\RateLimit\RateLimitStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FailedAuthRateLimiter::class)]
#[CoversClass(RateLimitStatus::class)]
final class FailedAuthRateLimiterTest extends TestCase
{
    public function testItBlocksAfterConfiguredAttempts(): void
    {
        $limiter = new FailedAuthRateLimiter(new ArrayCacheStore());

        $first = $limiter->registerFailure('login', '127.0.0.1|user@example.com', 3, 300);
        $second = $limiter->registerFailure('login', '127.0.0.1|user@example.com', 3, 300);
        $third = $limiter->registerFailure('login', '127.0.0.1|user@example.com', 3, 300);

        self::assertFalse($first->blocked);
        self::assertFalse($second->blocked);
        self::assertTrue($third->blocked);
        self::assertGreaterThan(0, $third->retryAfterSeconds);
    }

    public function testResetClearsBlockState(): void
    {
        $limiter = new FailedAuthRateLimiter(new ArrayCacheStore());
        $subject = '127.0.0.1|reset@example.com';

        $limiter->registerFailure('login', $subject, 1, 300);
        self::assertTrue($limiter->status('login', $subject, 1, 300)->blocked);

        $limiter->reset('login', $subject);

        self::assertFalse($limiter->status('login', $subject, 1, 300)->blocked);
    }
}
