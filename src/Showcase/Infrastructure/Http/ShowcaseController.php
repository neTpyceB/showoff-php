<?php

declare(strict_types=1);

namespace App\Showcase\Infrastructure\Http;

use App\Cache\CacheStore;
use App\Showcase\Application\Form\ShowcaseSettingsInput;
use App\Showcase\Application\Messaging\ShowcaseAuditMessage;
use App\Showcase\Application\Processor\ShowcaseExecutionContext;
use App\Showcase\Application\Processor\ShowcasePipelineRunner;
use App\Showcase\Application\Report\ShowcaseReport;
use App\Showcase\Application\Security\ShowcaseAccessDecider;
use App\Showcase\Infrastructure\Form\ShowcaseSettingsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/showcase')]
final class ShowcaseController extends AbstractController
{
    public function __construct(
        private readonly ShowcasePipelineRunner $runner,
        private readonly SerializerInterface $serializer,
        private readonly ShowcaseAccessDecider $accessDecider,
        private readonly FormFactoryInterface $forms,
        private readonly MessageBusInterface $bus,
        private readonly CacheStore $cache,
        private readonly string $moduleName,
    ) {}

    #[Route('/report', name: 'showcase_report', methods: ['GET'])]
    public function report(): JsonResponse
    {
        $results = $this->runner->run(new ShowcaseExecutionContext(
            moduleName: $this->moduleName,
            triggeredBy: 'http',
        ));

        $report = new ShowcaseReport(
            module: $this->moduleName,
            generatedAt: new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            results: $results,
        );

        $json = $this->serializer->serialize($report, 'json');
        try {
            $normalized = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $normalized = [];
        }

        return $this->json($this->normalizeArray($normalized));
    }

    #[Route('/diagnostics', name: 'showcase_diagnostics', methods: ['GET'])]
    public function diagnostics(Request $request): JsonResponse
    {
        $roles = $this->parseRoles($request->headers->get('X-Showcase-Roles'));
        if (!$this->accessDecider->canViewDiagnostics($roles)) {
            return $this->json([
                'error' => 'Forbidden.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        return $this->json([
            'module' => $this->moduleName,
            'middleware' => (bool) $request->attributes->get('_showcase.middleware', false),
            'trace' => $request->attributes->get('_showcase.trace'),
        ]);
    }

    #[Route('/audit', name: 'showcase_audit', methods: ['POST'])]
    public function audit(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        $action = is_string($payload['action'] ?? null) ? trim($payload['action']) : '';
        if ($action === '') {
            $action = 'showcase.audit.triggered';
        }

        $this->bus->dispatch(new ShowcaseAuditMessage(
            action: $action,
            occurredAt: new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
        ));

        return $this->json([
            'status' => 'accepted',
            'messagesTotal' => $this->intValue($this->cache->get('showcase:audit:messages_total')),
            'lastAction' => $this->cache->get('showcase:audit:last_action'),
        ]);
    }

    #[Route('/settings/validate', name: 'showcase_settings_validate', methods: ['POST'])]
    public function validateSettings(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        $input = new ShowcaseSettingsInput();
        $form = $this->forms->create(ShowcaseSettingsType::class, $input);
        $form->submit($payload);

        if ($form->isValid()) {
            return $this->json([
                'valid' => true,
                'data' => [
                    'code' => $input->code,
                    'notes' => $input->notes,
                ],
            ]);
        }

        return $this->json([
            'valid' => false,
            'errors' => $this->collectErrors($form),
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(Request $request): array
    {
        $content = trim($request->getContent());
        if ($content === '') {
            return [];
        }

        try {
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $this->normalizeArray($decoded) : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeArray(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $normalized = [];
        foreach ($payload as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * @return list<string>
     */
    private function parseRoles(?string $rolesHeader): array
    {
        if (!is_string($rolesHeader) || trim($rolesHeader) === '') {
            return [];
        }

        $roles = [];
        foreach (explode(',', $rolesHeader) as $role) {
            $trimmed = strtoupper(trim($role));
            if ($trimmed !== '') {
                $roles[] = $trimmed;
            }
        }

        return array_values(array_unique($roles));
    }

    /**
     * @return list<array{field: string, message: string}>
     */
    private function collectErrors(\Symfony\Component\Form\FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true, true) as $error) {
            if (!$error instanceof FormError) {
                continue;
            }

            $origin = $error->getOrigin();
            $errors[] = [
                'field' => $origin?->getName() ?? 'form',
                'message' => $error->getMessage(),
            ];
        }

        return $errors;
    }

    private function intValue(mixed $value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return (int) $value;
    }
}
