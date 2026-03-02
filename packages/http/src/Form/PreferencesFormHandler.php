<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Form;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class PreferencesFormHandler
{
    public const string FORM_NAME = 'preferences_form';

    private const array ALLOWED_THEMES = ['light', 'dark', 'system'];

    public function __construct(
        private FormTokenManager $tokenManager,
    ) {}

    public function handle(Request $request, SessionInterface $session): PreferencesFormResult
    {
        $errors = [];
        $theme = trim($request->request->getString('theme', 'system'));

        if (!$this->tokenManager->isValid($session, self::FORM_NAME, $request->request->getString('_token'))) {
            $errors['_token'] = 'The form token is invalid. Refresh the page and try again.';
        }

        if (!in_array($theme, self::ALLOWED_THEMES, true)) {
            $errors['theme'] = 'Select a supported theme option.';
        }

        return new PreferencesFormResult($errors === [], $theme, $errors);
    }
}
