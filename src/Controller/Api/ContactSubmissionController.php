<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\Rest\Request\CreateContactSubmissionRequest;
use App\Application\Contact\ApiContactSubmissionService;
use App\Application\Contact\ContactSubmissionStatsService;
use App\Security\ApiTokenService;
use Showoff\Core\Domain\Contact\ContactSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/contact-submissions')]
final class ContactSubmissionController extends AbstractController
{
    public function __construct(
        private readonly ContactSubmissionStatsService $stats,
        private readonly ApiContactSubmissionService $submissionService,
        private readonly ApiTokenService $apiTokens,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'api_contact_submission_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'data' => $this->stats->get(),
        ]);
    }

    #[Route('', name: 'api_contact_submission_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $apiUser = $this->apiTokens->userFromRequest($request);
        if ($apiUser === null) {
            return $this->json([
                'errors' => [
                    ['message' => 'Unauthorized. Bearer token required.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $this->decodePayload($request);
        if ($payload === null) {
            return $this->json([
                'errors' => [
                    [
                        'field' => 'body',
                        'message' => 'Request body must be valid JSON.',
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $form = new CreateContactSubmissionRequest();
        $form->name = trim($this->stringValue($payload, 'name'));
        $form->email = trim($this->stringValue($payload, 'email'));
        $form->message = trim($this->stringValue($payload, 'message'));

        $violations = $this->validator->validate($form);
        if (count($violations) > 0) {
            return $this->json([
                'errors' => $this->normalizeViolations($violations),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $submission = $this->submissionService->submit(
            name: $form->name,
            email: $form->email,
            message: $form->message,
            source: 'rest_api',
        );

        return $this->json([
            'data' => $this->normalizeSubmission($submission),
        ], Response::HTTP_CREATED);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodePayload(Request $request): ?array
    {
        $content = trim($request->getContent());
        if ($content === '') {
            return [];
        }

        try {
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($decoded)) {
            return null;
        }

        return $this->stringKeyArray($decoded);
    }

    /**
     * @return list<array{field: string, message: string}>
     */
    private function normalizeViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => (string) $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
            ];
        }

        return $errors;
    }

    /**
     * @return array{id: int, name: string, email: string, message: string, status: string, submittedAt: string}|null
     */
    private function normalizeSubmission(?ContactSubmission $submission): ?array
    {
        if ($submission === null || $submission->id === null) {
            return null;
        }

        return [
            'id' => $submission->id->value,
            'name' => $submission->name->value,
            'email' => $submission->email->value,
            'message' => $submission->message->value,
            'status' => $submission->status->value,
            'submittedAt' => $submission->submittedAt->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function stringValue(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<mixed, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function stringKeyArray(array $payload): array
    {
        $normalized = [];
        foreach ($payload as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
