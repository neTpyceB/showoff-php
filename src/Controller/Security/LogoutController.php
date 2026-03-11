<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Security\AuthService;
use App\Security\Csrf\FormCsrfTokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly FormCsrfTokenManager $csrfTokens,
    ) {}

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->csrfTokens->isValid(
            $request,
            'logout_form',
            $request->request->getString('_csrf_token'),
        )) {
            return new Response('Invalid CSRF token.', Response::HTTP_FORBIDDEN);
        }

        $this->authService->logout($request);

        return new RedirectResponse('/login', Response::HTTP_SEE_OTHER);
    }
}
