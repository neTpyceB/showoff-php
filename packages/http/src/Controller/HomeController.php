<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Controller;

use Showoff\Core\Config\AppConfig;
use Showoff\Core\Http\Session\WebSessionManager;
use Showoff\Core\Http\View\TwigViewRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class HomeController
{
    public function __construct(
        private AppConfig $config,
        private TwigViewRenderer $renderer,
        private WebSessionManager $sessionManager,
    ) {}

    public function __invoke(Request $request): Response
    {
        $requestCount = $this->sessionManager->increment($request, 'request_count');

        return new Response($this->renderer->render('pages/home.html.twig', [
            'app_name' => $this->config->appName,
            'current_route' => 'home',
            'theme' => $request->cookies->get('theme', 'system'),
            'flash_messages' => $this->sessionManager->consumeFlashes($request),
            'request_count' => $requestCount,
        ]));
    }
}
