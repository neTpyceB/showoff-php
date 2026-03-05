<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Controller;

use Showoff\Core\Config\AppConfig;
use Showoff\Core\Domain\Contact\ContactEmail;
use Showoff\Core\Domain\Contact\ContactMessage;
use Showoff\Core\Domain\Contact\ContactName;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionRepository;
use Showoff\Core\Domain\Contact\SubmitContactSubmission;
use Showoff\Core\Http\Form\ContactFormHandler;
use Showoff\Core\Http\Form\FormTokenManager;
use Showoff\Core\Http\Session\WebSessionManager;
use Showoff\Core\Http\View\TwigViewRenderer;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class ContactController
{
    public function __construct(
        private AppConfig $config,
        private TwigViewRenderer $renderer,
        private WebSessionManager $sessionManager,
        private ContactFormHandler $formHandler,
        private FormTokenManager $tokenManager,
        private SubmitContactSubmission $submitContactSubmission,
        private ContactSubmissionRepository $submissionRepository,
    ) {}

    public function __invoke(Request $request): Response
    {
        $session = $this->sessionManager->start($request);

        if ($request->isMethod(Request::METHOD_POST)) {
            $result = $this->formHandler->handle($request, $session);

            if ($result->isValid) {
                if ($result->data === null) {
                    return new Response('Invalid form state.', Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                $this->submitContactSubmission->submit(
                    name: new ContactName($result->data->name),
                    email: new ContactEmail($result->data->email),
                    message: new ContactMessage($result->data->message),
                );
                $this->sessionManager->addFlash($request, 'success', 'Contact form submitted successfully.');

                $response = new RedirectResponse('/contact', Response::HTTP_SEE_OTHER);
                $response->headers->setCookie(Cookie::create(
                    name: 'last_contact_email',
                    value: $result->data->email,
                    secure: $this->config->sessionCookieSecure,
                    httpOnly: false,
                    sameSite: Cookie::SAMESITE_LAX,
                ));

                return $response;
            }

            return new Response($this->renderer->render('pages/contact.html.twig', [
                'app_name' => $this->config->appName,
                'current_route' => 'contact',
                'flash_messages' => $this->sessionManager->consumeFlashes($request),
                'csrf_token' => $this->tokenManager->tokenFor($session, ContactFormHandler::FORM_NAME),
                'errors' => $result->errors,
                'values' => $result->submittedValues,
                'submission_count' => $this->submissionRepository->countAll(),
                'last_contact_email' => $request->cookies->get('last_contact_email'),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new Response($this->renderer->render('pages/contact.html.twig', [
            'app_name' => $this->config->appName,
            'current_route' => 'contact',
            'flash_messages' => $this->sessionManager->consumeFlashes($request),
            'csrf_token' => $this->tokenManager->tokenFor($session, ContactFormHandler::FORM_NAME),
            'errors' => [],
            'values' => [
                'name' => '',
                'email' => $request->cookies->get('last_contact_email', ''),
                'message' => '',
            ],
            'submission_count' => $this->submissionRepository->countAll(),
            'last_contact_email' => $request->cookies->get('last_contact_email'),
        ]));
    }
}
