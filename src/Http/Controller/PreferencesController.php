<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Controller;

use Showoff\Core\Config\AppConfig;
use Showoff\Core\Http\Form\FormTokenManager;
use Showoff\Core\Http\Form\PreferencesFormHandler;
use Showoff\Core\Http\Session\WebSessionManager;
use Showoff\Core\Http\View\TwigViewRenderer;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class PreferencesController
{
    public function __construct(
        private AppConfig $config,
        private TwigViewRenderer $renderer,
        private WebSessionManager $sessionManager,
        private PreferencesFormHandler $formHandler,
        private FormTokenManager $tokenManager,
    ) {}

    public function __invoke(Request $request): Response
    {
        $session = $this->sessionManager->start($request);

        if ($request->isMethod(Request::METHOD_POST)) {
            $result = $this->formHandler->handle($request, $session);

            if ($result->isValid) {
                $this->sessionManager->addFlash($request, 'success', 'Preferences updated.');

                $response = new RedirectResponse('/preferences', Response::HTTP_SEE_OTHER);
                $response->headers->setCookie(Cookie::create(
                    name: 'theme',
                    value: $result->theme,
                    secure: $this->config->sessionCookieSecure,
                    httpOnly: false,
                    sameSite: Cookie::SAMESITE_LAX,
                ));

                return $response;
            }

            return new Response($this->renderer->render('pages/preferences.html.twig', [
                'app_name' => $this->config->appName,
                'current_route' => 'preferences',
                'flash_messages' => $this->sessionManager->consumeFlashes($request),
                'csrf_token' => $this->tokenManager->tokenFor($session, PreferencesFormHandler::FORM_NAME),
                'errors' => $result->errors,
                'selected_theme' => $request->request->getString('theme', 'system'),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new Response($this->renderer->render('pages/preferences.html.twig', [
            'app_name' => $this->config->appName,
            'current_route' => 'preferences',
            'flash_messages' => $this->sessionManager->consumeFlashes($request),
            'csrf_token' => $this->tokenManager->tokenFor($session, PreferencesFormHandler::FORM_NAME),
            'errors' => [],
            'selected_theme' => $request->cookies->get('theme', 'system'),
        ]));
    }
}
