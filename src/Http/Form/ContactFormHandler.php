<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Form;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class ContactFormHandler
{
    public const string FORM_NAME = 'contact_form';

    public function __construct(
        private FormTokenManager $tokenManager,
    ) {}

    public function handle(Request $request, SessionInterface $session): ContactFormResult
    {
        $values = [
            'name' => trim($request->request->getString('name')),
            'email' => trim($request->request->getString('email')),
            'message' => trim($request->request->getString('message')),
        ];
        $errors = [];

        if (!$this->tokenManager->isValid($session, self::FORM_NAME, $request->request->getString('_token'))) {
            $errors['_token'] = 'The form token is invalid. Refresh the page and try again.';
        }

        if ($values['name'] === '') {
            $errors['name'] = 'Name is required.';
        }

        if (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'A valid email address is required.';
        }

        if ($values['message'] === '' || mb_strlen($values['message']) < 10) {
            $errors['message'] = 'Message must be at least 10 characters.';
        }

        if ($errors !== []) {
            return new ContactFormResult(false, null, $errors, $values);
        }

        return new ContactFormResult(
            true,
            new ContactFormData($values['name'], $values['email'], $values['message']),
            [],
            $values,
        );
    }
}
