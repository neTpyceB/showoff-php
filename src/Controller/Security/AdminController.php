<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Security\AuthorizationService;
use App\Security\AuthService;
use App\Security\Role;
use Showoff\Core\Config\AppConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly AuthorizationService $authorization,
    ) {}

    #[Route('/admin', name: 'app_admin', methods: ['GET'])]
    public function __invoke(\Symfony\Component\HttpFoundation\Request $request, AppConfig $config): Response
    {
        $user = $this->authService->user($request);
        if ($user === null) {
            return new RedirectResponse('/login', Response::HTTP_SEE_OTHER);
        }

        $this->authorization->assertRole($user, Role::ADMIN);

        return $this->render('pages/admin.html.twig', [
            'app_name' => $config->appName,
            'current_route' => 'admin',
            'flash_messages' => [],
            'email' => $user->email,
            'role' => $user->role->value,
        ]);
    }
}
