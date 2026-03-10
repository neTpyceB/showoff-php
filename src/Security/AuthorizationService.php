<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class AuthorizationService
{
    public function assertRole(?User $user, Role $role): void
    {
        if (!$user instanceof User || $user->role !== $role) {
            throw new AccessDeniedHttpException('Insufficient permissions.');
        }
    }
}
