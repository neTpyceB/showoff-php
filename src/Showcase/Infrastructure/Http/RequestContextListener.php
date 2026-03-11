<?php

declare(strict_types=1);

namespace App\Showcase\Infrastructure\Http;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 512)]
final class RequestContextListener
{
    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->attributes->has('_showcase.trace')) {
            $request->attributes->set('_showcase.trace', bin2hex(random_bytes(8)));
        }
    }
}
