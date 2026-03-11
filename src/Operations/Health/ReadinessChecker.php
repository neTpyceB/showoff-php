<?php

declare(strict_types=1);

namespace App\Operations\Health;

use App\Cache\CacheStore;
use PDO;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Health\SystemHealthChecker;

final readonly class ReadinessChecker
{
    public function __construct(
        private SystemHealthChecker $systemHealthChecker,
        private AppConfig $config,
        private PDO $connection,
        private CacheStore $cache,
    ) {}

    /**
     * @return array{
     *     status: 'ready'|'degraded',
     *     checks: array{
     *         runtime: bool,
     *         database: bool,
     *         cache: bool
     *     },
     *     timestamp: string
     * }
     */
    public function check(): array
    {
        $checks = [
            'runtime' => $this->systemHealthChecker->check($this->config)->isHealthy(),
            'database' => $this->databaseAvailable(),
            'cache' => $this->cacheAvailable(),
        ];

        return [
            'status' => in_array(false, $checks, true) ? 'degraded' : 'ready',
            'checks' => $checks,
            'timestamp' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
        ];
    }

    private function databaseAvailable(): bool
    {
        try {
            $statement = $this->connection->query('SELECT 1');
            if ($statement === false) {
                return false;
            }

            return (int) $statement->fetchColumn() === 1;
        } catch (\Throwable) {
            return false;
        }
    }

    private function cacheAvailable(): bool
    {
        $key = 'ops:readiness:probe';
        $value = bin2hex(random_bytes(8));

        try {
            $this->cache->set($key, $value, 10);
            $cached = $this->cache->get($key);
            $this->cache->delete($key);
        } catch (\Throwable) {
            return false;
        }

        return is_string($cached) && hash_equals($value, $cached);
    }
}
