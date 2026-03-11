<?php

declare(strict_types=1);

namespace App\Security\Csrf;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class FormCsrfTokenManager
{
    private const SESSION_KEY = '_csrf_tokens';

    public function tokenFor(Request $request, string $formName): string
    {
        $session = $this->session($request);
        if (!$session instanceof SessionInterface) {
            throw new \RuntimeException('Session is required for CSRF token generation.');
        }

        /** @var array<string, string> $tokens */
        $tokens = $session->get(self::SESSION_KEY, []);
        if (!isset($tokens[$formName])) {
            $tokens[$formName] = bin2hex(random_bytes(32));
            $session->set(self::SESSION_KEY, $tokens);
        }

        return $tokens[$formName];
    }

    public function isValid(Request $request, string $formName, ?string $submittedToken): bool
    {
        if (!is_string($submittedToken) || trim($submittedToken) === '') {
            return false;
        }

        $session = $this->session($request);
        if (!$session instanceof SessionInterface) {
            return false;
        }

        /** @var array<string, string> $tokens */
        $tokens = $session->get(self::SESSION_KEY, []);

        return isset($tokens[$formName]) && hash_equals($tokens[$formName], trim($submittedToken));
    }

    private function session(Request $request): ?SessionInterface
    {
        if (!$request->hasSession()) {
            return null;
        }

        return $request->getSession();
    }
}
