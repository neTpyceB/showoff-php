<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Concurrency;

use App\Infrastructure\Lock\ArrayRequestLockManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayRequestLockManager::class)]
final class ArrayRequestLockManagerTest extends TestCase
{
    public function testItPreventsConcurrentAcquireUntilReleased(): void
    {
        $key = 'lock-key-' . bin2hex(random_bytes(8));
        $managerA = new ArrayRequestLockManager();
        $managerB = new ArrayRequestLockManager();

        self::assertTrue($managerA->acquire($key, 10));
        self::assertFalse($managerB->acquire($key, 10));

        $managerA->release($key);

        self::assertTrue($managerB->acquire($key, 10));
        $managerB->release($key);
    }

    public function testItAllowsAcquireAfterTtlExpires(): void
    {
        $key = 'lock-ttl-' . bin2hex(random_bytes(8));
        $managerA = new ArrayRequestLockManager();
        $managerB = new ArrayRequestLockManager();

        self::assertTrue($managerA->acquire($key, 1));
        usleep(1_100_000);

        self::assertTrue($managerB->acquire($key, 1));
        $managerB->release($key);
    }
}
