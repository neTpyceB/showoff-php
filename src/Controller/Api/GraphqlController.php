<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\Graphql\GraphqlSchemaProvider;
use App\Performance\Http\JsonHttpCacheService;
use App\Performance\Idempotency\IdempotencyLockException;
use App\Performance\Idempotency\IdempotencyService;
use App\Security\ApiTokenService;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/graphql', name: 'api_graphql', methods: ['POST'])]
final class GraphqlController extends AbstractController
{
    public function __construct(
        private readonly GraphqlSchemaProvider $schemaProvider,
        private readonly ApiTokenService $apiTokens,
        private readonly JsonHttpCacheService $httpCache,
        private readonly IdempotencyService $idempotency,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload === null) {
            return $this->json([
                'errors' => [
                    ['message' => 'Request body must be valid JSON.'],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $query = $payload['query'] ?? null;
        if (!is_string($query) || trim($query) === '') {
            return $this->json([
                'errors' => [
                    ['message' => 'query is required.'],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $isMutation = $this->isMutation($query);
        if ($isMutation && $this->apiTokens->userFromRequest($request) === null) {
            return $this->json([
                'errors' => [
                    ['message' => 'Unauthorized. Bearer token required for mutations.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $variables = $this->normalizeVariables($payload['variables'] ?? null);

        $operationName = $payload['operationName'] ?? null;
        if (!is_string($operationName)) {
            $operationName = null;
        }

        if (!$isMutation) {
            return $this->httpCache->createCacheableResponse(
                $request,
                $this->executeGraphql($query, $variables, $operationName),
            );
        }

        try {
            $idempotencyKey = $this->idempotencyKey($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->json([
                'errors' => [
                    ['message' => $exception->getMessage()],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $runMutation = fn(): JsonResponse => new JsonResponse(
            $this->executeGraphql($query, $variables, $operationName),
        );

        if ($idempotencyKey === null) {
            return $runMutation();
        }

        try {
            return $this->idempotency->execute('graphql.mutation', $idempotencyKey, $runMutation);
        } catch (IdempotencyLockException) {
            return $this->json([
                'errors' => [
                    ['message' => 'A request with this Idempotency-Key is currently in progress.'],
                ],
            ], Response::HTTP_CONFLICT);
        }
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
     * @return array<string, mixed>
     */
    private function normalizeVariables(mixed $variables): array
    {
        if (!is_array($variables)) {
            return [];
        }

        return $this->stringKeyArray($variables);
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

    private function isMutation(string $query): bool
    {
        return str_contains(strtolower($query), 'mutation');
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @return array<string, mixed>
     */
    private function executeGraphql(string $query, array $variables, ?string $operationName): array
    {
        try {
            $result = GraphQL::executeQuery(
                $this->schemaProvider->schema(),
                $query,
                variableValues: $variables,
                operationName: $operationName,
            );

            return $this->stringKeyArray($result->toArray());
        } catch (\Throwable $exception) {
            return [
                'errors' => [
                    FormattedError::createFromException($exception),
                ],
            ];
        }
    }

    private function idempotencyKey(Request $request): ?string
    {
        $header = $request->headers->get('Idempotency-Key');
        if ($header === null) {
            return null;
        }

        $key = trim($header);
        if ($key === '') {
            throw new \InvalidArgumentException('Idempotency-Key header must not be empty.');
        }

        if (strlen($key) > 128) {
            throw new \InvalidArgumentException('Idempotency-Key header must not exceed 128 characters.');
        }

        return $key;
    }
}
