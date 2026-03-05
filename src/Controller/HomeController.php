<?php

declare(strict_types=1);

namespace App\Controller;

use Showoff\Core\Config\AppConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function __invoke(Request $request, AppConfig $config): Response
    {
        $requestCount = 1;
        if ($request->hasSession()) {
            $session = $request->getSession();
            if ($session instanceof Session) {
                $existingCount = $session->get('home_request_count', 0);
                $requestCount = is_int($existingCount) ? $existingCount + 1 : 1;
                $session->set('home_request_count', $requestCount);
            }
        }

        return $this->render('pages/home.html.twig', [
            'app_name' => $config->appName,
            'current_route' => 'home',
            'flash_messages' => $this->flashMessages($request),
            'request_count' => $requestCount,
            'theme_cookie' => $request->cookies->get('theme', 'system'),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    private function flashMessages(Request $request): array
    {
        if (!$request->hasSession()) {
            return [];
        }

        $session = $request->getSession();
        if (!$session instanceof Session) {
            return [];
        }

        $messages = [];
        foreach ($session->getFlashBag()->all() as $type => $group) {
            if (!is_string($type) || !is_array($group)) {
                continue;
            }

            $messages[$type] = array_values(array_filter(
                $group,
                static fn(mixed $message): bool => is_string($message),
            ));
        }

        return $messages;
    }
}
