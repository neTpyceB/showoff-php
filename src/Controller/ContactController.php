<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Contact\ContactSubmissionApplicationService;
use App\Http\Form\ContactRequest;
use App\Security\Csrf\FormCsrfTokenManager;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly ContactSubmissionApplicationService $submissionService,
        private readonly ContactSubmissionRepository $submissionRepository,
        private readonly ValidatorInterface $validator,
        private readonly FormCsrfTokenManager $csrfTokens,
    ) {}

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, AppConfig $config): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $form = new ContactRequest();
            $form->name = trim($request->request->getString('name'));
            $form->email = trim($request->request->getString('email'));
            $form->message = trim($request->request->getString('message'));
            $errors = [];

            if (!$this->csrfTokens->isValid(
                $request,
                'contact_form',
                $request->request->getString('_csrf_token'),
            )) {
                $errors['_csrf_token'] = 'Invalid form token. Refresh the page and try again.';
            }

            $violations = $this->validator->validate($form);
            $errors = array_replace($errors, $this->errors($violations));

            if ($errors === []) {
                $this->submissionService->submit($form, $request);
                $this->addFlash('success', 'Contact form submitted successfully.');

                $response = new RedirectResponse('/contact', Response::HTTP_SEE_OTHER);
                $response->headers->setCookie(Cookie::create(
                    name: 'last_contact_email',
                    value: $form->email,
                    secure: $config->sessionCookieSecure,
                    httpOnly: true,
                    sameSite: Cookie::SAMESITE_LAX,
                ));

                return $response;
            }

            return $this->render('pages/contact.html.twig', [
                'app_name' => $config->appName,
                'current_route' => 'contact',
                'flash_messages' => $this->flashMessages($request),
                'errors' => $errors,
                'values' => [
                    'name' => $form->name,
                    'email' => $form->email,
                    'message' => $form->message,
                ],
                'submission_count' => $this->submissionRepository->countAll(),
                'last_contact_email' => $request->cookies->get('last_contact_email'),
                'csrf_token' => $this->csrfTokens->tokenFor($request, 'contact_form'),
            ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        return $this->render('pages/contact.html.twig', [
            'app_name' => $config->appName,
            'current_route' => 'contact',
            'flash_messages' => $this->flashMessages($request),
            'errors' => [],
            'values' => [
                'name' => '',
                'email' => $request->cookies->get('last_contact_email', ''),
                'message' => '',
            ],
            'submission_count' => $this->submissionRepository->countAll(),
            'last_contact_email' => $request->cookies->get('last_contact_email'),
            'csrf_token' => $this->csrfTokens->tokenFor($request, 'contact_form'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function errors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $path = (string) $violation->getPropertyPath();
            $errors[$path] = (string) $violation->getMessage();
        }

        return $errors;
    }

    /**
     * @return array<string, list<string>>
     */
    private function flashMessages(Request $request): array
    {
        if (!$request->hasSession()) {
            return [];
        }

        $session = $request->getSession();
        if (!$session instanceof Session) {
            return [];
        }

        $messages = [];
        foreach ($session->getFlashBag()->all() as $type => $group) {
            if (!is_string($type) || !is_array($group)) {
                continue;
            }

            $messages[$type] = array_values(array_filter(
                $group,
                static fn(mixed $message): bool => is_string($message),
            ));
        }

        return $messages;
    }
}
