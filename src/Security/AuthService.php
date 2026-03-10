<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

final readonly class AuthService
{
    private const SESSION_USER_ID = '_auth_user_id';
    private const SESSION_USER_ROLE = '_auth_user_role';

    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
    ) {}

    public function authenticate(Request $request, string $email, string $password): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user instanceof User) {
            return false;
        }

        if (!$this->passwordHasher->verify($password, $user->passwordHash)) {
            return false;
        }

        $session = $this->session($request);
        if (!$session instanceof Session) {
            return false;
        }

        $session->migrate(true);
        $session->set(self::SESSION_USER_ID, $user->id);
        $session->set(self::SESSION_USER_ROLE, $user->role->value);

        return true;
    }

    public function logout(Request $request): void
    {
        $session = $this->session($request);
        if (!$session instanceof Session) {
            return;
        }

        $session->invalidate();
    }

    public function user(Request $request): ?User
    {
        $session = $this->session($request);
        if (!$session instanceof Session) {
            return null;
        }

        $userId = $session->get(self::SESSION_USER_ID);
        if (!is_int($userId)) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    private function session(Request $request): ?Session
    {
        if (!$request->hasSession()) {
            return null;
        }

        $session = $request->getSession();

        return $session instanceof Session ? $session : null;
    }
}
