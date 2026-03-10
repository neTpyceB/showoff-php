<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Security\AuthService;
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
    ) {}

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, AppConfig $config): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $email = trim($request->request->getString('email'));
            $password = $request->request->getString('password');

            if ($this->authService->authenticate($request, $email, $password)) {
                return new RedirectResponse('/admin', Response::HTTP_SEE_OTHER);
            }

            return $this->render('pages/login.html.twig', [
                'app_name' => $config->appName,
                'current_route' => 'login',
                'flash_messages' => [],
                'error' => 'Invalid credentials.',
                'email' => $email,
            ], new Response(status: Response::HTTP_UNAUTHORIZED));
        }

        return $this->render('pages/login.html.twig', [
            'app_name' => $config->appName,
            'current_route' => 'login',
            'flash_messages' => [],
            'error' => null,
            'email' => '',
        ]);
    }
}
