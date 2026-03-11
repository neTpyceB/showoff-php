<?php

declare(strict_types=1);

namespace App\Showcase\Infrastructure\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ResponseHeaderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $moduleName,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $response->headers->set('X-Showcase-Module', $this->moduleName);

        $trace = $request->attributes->get('_showcase.trace');
        if (is_string($trace) && $trace !== '') {
            $response->headers->set('X-Showcase-Trace', $trace);
        }
    }
}
