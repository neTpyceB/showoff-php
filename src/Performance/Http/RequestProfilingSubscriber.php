<?php

declare(strict_types=1);

namespace App\Performance\Http;

use App\Observability\Metrics\RequestMetricsStore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class RequestProfilingSubscriber implements EventSubscriberInterface
{
    private const START_TIME_ATTRIBUTE = '_app.performance.start_ns';
    private const START_MEMORY_ATTRIBUTE = '_app.performance.start_memory';
    private const REQUEST_ID_ATTRIBUTE = '_app.observability.request_id';

    public function __construct(
        private int $slowRequestThresholdMs,
        private ?RequestMetricsStore $metrics = null,
        private bool $structuredLoggingEnabled = false,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $request->attributes->set(self::START_TIME_ATTRIBUTE, hrtime(true));
        $request->attributes->set(self::START_MEMORY_ATTRIBUTE, memory_get_usage(true));
        $request->attributes->set(
            self::REQUEST_ID_ATTRIBUTE,
            $this->normalizeRequestId($request->headers->get('X-Request-Id')) ?? $this->generateRequestId(),
        );
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $startNs = $request->attributes->get(self::START_TIME_ATTRIBUTE);
        $startMemory = $request->attributes->get(self::START_MEMORY_ATTRIBUTE);

        if (!is_int($startNs) || !is_int($startMemory)) {
            return;
        }

        $durationMs = (hrtime(true) - $startNs) / 1_000_000;
        $memoryDeltaKb = (memory_get_usage(true) - $startMemory) / 1024;

        $requestId = $request->attributes->get(self::REQUEST_ID_ATTRIBUTE);
        if (!is_string($requestId) || $requestId === '') {
            $requestId = $this->generateRequestId();
        }

        $response = $event->getResponse();
        $response->headers->set('X-Response-Time-Ms', number_format($durationMs, 3, '.', ''));
        $response->headers->set('X-Memory-Delta-Kb', number_format($memoryDeltaKb, 1, '.', ''));
        $response->headers->set('X-Request-Id', $requestId);

        if ($this->metrics instanceof RequestMetricsStore) {
            $this->metrics->record($response->getStatusCode(), $durationMs);
        }

        if ($this->structuredLoggingEnabled) {
            $this->logRequest(
                method: $request->getMethod(),
                path: $request->getPathInfo(),
                statusCode: $response->getStatusCode(),
                durationMs: $durationMs,
                memoryDeltaKb: $memoryDeltaKb,
                requestId: $requestId,
            );
        }
    }

    private function logRequest(
        string $method,
        string $path,
        int $statusCode,
        float $durationMs,
        float $memoryDeltaKb,
        string $requestId,
    ): void {
        try {
            $payload = json_encode([
                'type' => 'http_request',
                'requestId' => $requestId,
                'method' => $method,
                'path' => $path,
                'statusCode' => $statusCode,
                'durationMs' => round($durationMs, 3),
                'memoryDeltaKb' => round($memoryDeltaKb, 1),
                'slow' => $durationMs >= $this->slowRequestThresholdMs,
                'timestamp' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            ], JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return;
        }

        error_log($payload);
    }

    private function normalizeRequestId(?string $requestId): ?string
    {
        if (!is_string($requestId)) {
            return null;
        }

        $trimmed = trim($requestId);
        if (preg_match('/^[A-Za-z0-9_-]{8,120}$/', $trimmed) !== 1) {
            return null;
        }

        return $trimmed;
    }

    private function generateRequestId(): string
    {
        return bin2hex(random_bytes(16));
    }
}
