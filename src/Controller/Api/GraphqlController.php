<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\Graphql\GraphqlSchemaProvider;
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

        $variables = $this->normalizeVariables($payload['variables'] ?? null);

        $operationName = $payload['operationName'] ?? null;
        if (!is_string($operationName)) {
            $operationName = null;
        }

        try {
            $result = GraphQL::executeQuery(
                $this->schemaProvider->schema(),
                $query,
                variableValues: $variables,
                operationName: $operationName,
            );
            $output = $result->toArray();
        } catch (\Throwable $exception) {
            $output = [
                'errors' => [
                    FormattedError::createFromException($exception),
                ],
            ];
        }

        return new JsonResponse($output);
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
}
