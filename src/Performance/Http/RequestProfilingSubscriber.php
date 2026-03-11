<?php

declare(strict_types=1);

namespace App\Performance\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class RequestProfilingSubscriber implements EventSubscriberInterface
{
    private const START_TIME_ATTRIBUTE = '_app.performance.start_ns';
    private const START_MEMORY_ATTRIBUTE = '_app.performance.start_memory';

    public function __construct(
        private int $slowRequestThresholdMs,
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

        $response = $event->getResponse();
        $response->headers->set('X-Response-Time-Ms', number_format($durationMs, 3, '.', ''));
        $response->headers->set('X-Memory-Delta-Kb', number_format($memoryDeltaKb, 1, '.', ''));

        if ($durationMs >= $this->slowRequestThresholdMs) {
            error_log(sprintf(
                '[performance] slow_request method=%s path=%s status=%d duration_ms=%.3f memory_delta_kb=%.1f',
                $request->getMethod(),
                $request->getPathInfo(),
                $response->getStatusCode(),
                $durationMs,
                $memoryDeltaKb,
            ));
        }
    }
}
