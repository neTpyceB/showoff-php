<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Form;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class FormTokenManager
{
    public function tokenFor(SessionInterface $session, string $formName): string
    {
        /** @var array<string, string> $tokens */
        $tokens = $session->get('form_tokens', []);

        if (!isset($tokens[$formName])) {
            $tokens[$formName] = bin2hex(random_bytes(16));
            $session->set('form_tokens', $tokens);
        }

        return $tokens[$formName];
    }

    public function isValid(SessionInterface $session, string $formName, string $submittedToken): bool
    {
        /** @var array<string, string> $tokens */
        $tokens = $session->get('form_tokens', []);

        return isset($tokens[$formName]) && hash_equals($tokens[$formName], $submittedToken);
    }
}
