<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Security\AuthService;
use App\Security\Csrf\FormCsrfTokenManager;
use App\Security\RateLimit\FailedAuthRateLimiter;
use Showoff\Core\Config\AppConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly FormCsrfTokenManager $csrfTokens,
        private readonly FailedAuthRateLimiter $rateLimiter,
        private readonly int $maxAttempts,
        private readonly int $windowSeconds,
    ) {}

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, AppConfig $config): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $email = trim($request->request->getString('email'));
            $password = $request->request->getString('password');
            $limiterSubject = $this->limiterSubject($request, $email);
            $rateLimitStatus = $this->rateLimiter->status('login', $limiterSubject, $this->maxAttempts, $this->windowSeconds);

            if ($rateLimitStatus->blocked) {
                return $this->render('pages/login.html.twig', [
                    'app_name' => $config->appName,
                    'current_route' => 'login',
                    'flash_messages' => [],
                    'error' => 'Too many failed sign-in attempts. Try again later.',
                    'email' => $email,
                    'csrf_token' => $this->csrfTokens->tokenFor($request, 'login_form'),
                ], new Response(
                    status: Response::HTTP_TOO_MANY_REQUESTS,
                    headers: ['Retry-After' => (string) $rateLimitStatus->retryAfterSeconds],
                ));
            }

            if (!$this->csrfTokens->isValid(
                $request,
                'login_form',
                $request->request->getString('_csrf_token'),
            )) {
                return $this->render('pages/login.html.twig', [
                    'app_name' => $config->appName,
                    'current_route' => 'login',
                    'flash_messages' => [],
                    'error' => 'Invalid form token. Refresh and try again.',
                    'email' => $email,
                    'csrf_token' => $this->csrfTokens->tokenFor($request, 'login_form'),
                ], new Response(status: Response::HTTP_FORBIDDEN));
            }

            if ($this->authService->authenticate($request, $email, $password)) {
                $this->rateLimiter->reset('login', $limiterSubject);

                return new RedirectResponse('/admin', Response::HTTP_SEE_OTHER);
            }

            $postFailure = $this->rateLimiter->registerFailure(
                'login',
                $limiterSubject,
                $this->maxAttempts,
                $this->windowSeconds,
            );

            if ($postFailure->blocked) {
                return $this->render('pages/login.html.twig', [
                    'app_name' => $config->appName,
                    'current_route' => 'login',
                    'flash_messages' => [],
                    'error' => 'Too many failed sign-in attempts. Try again later.',
                    'email' => $email,
                    'csrf_token' => $this->csrfTokens->tokenFor($request, 'login_form'),
                ], new Response(
                    status: Response::HTTP_TOO_MANY_REQUESTS,
                    headers: ['Retry-After' => (string) $postFailure->retryAfterSeconds],
                ));
            }

            return $this->render('pages/login.html.twig', [
                'app_name' => $config->appName,
                'current_route' => 'login',
                'flash_messages' => [],
                'error' => 'Invalid credentials.',
                'email' => $email,
                'csrf_token' => $this->csrfTokens->tokenFor($request, 'login_form'),
            ], new Response(status: Response::HTTP_UNAUTHORIZED));
        }

        return $this->render('pages/login.html.twig', [
            'app_name' => $config->appName,
            'current_route' => 'login',
            'flash_messages' => [],
            'error' => null,
            'email' => '',
            'csrf_token' => $this->csrfTokens->tokenFor($request, 'login_form'),
        ]);
    }

    private function limiterSubject(Request $request, string $email): string
    {
        $clientIp = $request->getClientIp() ?? 'unknown';

        return $clientIp . '|' . strtolower($email);
    }
}
