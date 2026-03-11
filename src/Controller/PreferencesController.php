<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Form\PreferencesRequest;
use App\Security\Csrf\FormCsrfTokenManager;
use Showoff\Core\Config\AppConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PreferencesController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly FormCsrfTokenManager $csrfTokens,
    ) {}

    #[Route('/preferences', name: 'app_preferences', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, AppConfig $config): Response
    {
        $selectedTheme = $request->cookies->get('theme', 'system');
        $errors = [];

        if ($request->isMethod(Request::METHOD_POST)) {
            $form = new PreferencesRequest();
            $form->theme = $request->request->getString('theme');
            if (!$this->csrfTokens->isValid(
                $request,
                'preferences_form',
                $request->request->getString('_csrf_token'),
            )) {
                $errors['theme'] = 'Invalid form token. Refresh the page and try again.';
            } else {
                $violations = $this->validator->validate($form);

                if (count($violations) === 0) {
                    $this->addFlash('success', 'Preferences updated.');
                    $response = new RedirectResponse('/preferences', Response::HTTP_SEE_OTHER);
                    $response->headers->setCookie(Cookie::create(
                        name: 'theme',
                        value: $form->theme,
                        secure: $config->sessionCookieSecure,
                        httpOnly: true,
                        sameSite: Cookie::SAMESITE_LAX,
                    ));

                    return $response;
                }

                foreach ($violations as $violation) {
                    $errors['theme'] = (string) $violation->getMessage();
                    break;
                }
            }

            $selectedTheme = $form->theme;
        }

        return $this->render('pages/preferences.html.twig', [
            'app_name' => $config->appName,
            'current_route' => 'preferences',
            'flash_messages' => $this->flashMessages($request),
            'selected_theme' => $selectedTheme,
            'errors' => $errors,
            'csrf_token' => $this->csrfTokens->tokenFor($request, 'preferences_form'),
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
