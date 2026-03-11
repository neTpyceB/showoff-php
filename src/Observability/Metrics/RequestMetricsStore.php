<?php

declare(strict_types=1);

namespace App\Observability\Metrics;

use App\Cache\CacheStore;

final readonly class RequestMetricsStore
{
    private const KEY_TOTAL = 'metrics:http:requests_total';
    private const KEY_CLIENT_ERRORS = 'metrics:http:client_errors_total';
    private const KEY_SERVER_ERRORS = 'metrics:http:server_errors_total';
    private const KEY_SLOW = 'metrics:http:slow_requests_total';

    public function __construct(
        private CacheStore $cache,
        private int $slowRequestThresholdMs,
    ) {}

    public function record(int $statusCode, float $durationMs): void
    {
        $this->increment(self::KEY_TOTAL);

        if ($statusCode >= 400 && $statusCode < 500) {
            $this->increment(self::KEY_CLIENT_ERRORS);
        }

        if ($statusCode >= 500) {
            $this->increment(self::KEY_SERVER_ERRORS);
        }

        if ($durationMs >= $this->slowRequestThresholdMs) {
            $this->increment(self::KEY_SLOW);
        }
    }

    /**
     * @return array{requestsTotal: int, clientErrorsTotal: int, serverErrorsTotal: int, slowRequestsTotal: int}
     */
    public function snapshot(): array
    {
        return [
            'requestsTotal' => $this->value(self::KEY_TOTAL),
            'clientErrorsTotal' => $this->value(self::KEY_CLIENT_ERRORS),
            'serverErrorsTotal' => $this->value(self::KEY_SERVER_ERRORS),
            'slowRequestsTotal' => $this->value(self::KEY_SLOW),
        ];
    }

    public function toPrometheus(): string
    {
        $snapshot = $this->snapshot();

        return implode("\n", [
            '# HELP app_http_requests_total Total HTTP requests.',
            '# TYPE app_http_requests_total counter',
            sprintf('app_http_requests_total %d', $snapshot['requestsTotal']),
            '# HELP app_http_client_errors_total Total 4xx responses.',
            '# TYPE app_http_client_errors_total counter',
            sprintf('app_http_client_errors_total %d', $snapshot['clientErrorsTotal']),
            '# HELP app_http_server_errors_total Total 5xx responses.',
            '# TYPE app_http_server_errors_total counter',
            sprintf('app_http_server_errors_total %d', $snapshot['serverErrorsTotal']),
            '# HELP app_http_slow_requests_total Total slow requests.',
            '# TYPE app_http_slow_requests_total counter',
            sprintf('app_http_slow_requests_total %d', $snapshot['slowRequestsTotal']),
            '',
        ]);
    }

    private function increment(string $key): void
    {
        try {
            $this->cache->increment($key);
        } catch (\Throwable) {
        }
    }

    private function value(string $key): int
    {
        try {
            $value = $this->cache->get($key);
        } catch (\Throwable) {
            return 0;
        }

        return is_numeric($value) ? (int) $value : 0;
    }
}
