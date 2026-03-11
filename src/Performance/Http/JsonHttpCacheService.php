<?php

declare(strict_types=1);

namespace App\Performance\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class JsonHttpCacheService
{
    public function __construct(
        private int $defaultMaxAge,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function createCacheableResponse(
        Request $request,
        array $payload,
        int $status = JsonResponse::HTTP_OK,
        ?int $maxAge = null,
    ): JsonResponse {
        $response = new JsonResponse($payload, $status);
        $response->setPrivate();
        $response->setMaxAge(max(1, $maxAge ?? $this->defaultMaxAge));
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->setEtag($this->etag($payload));

        if ($request->isMethodCacheable()) {
            $response->isNotModified($request);

            return $response;
        }

        if ($this->etagMatchesRequest($request, $response->getEtag())) {
            $response->setNotModified();
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function etag(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function etagMatchesRequest(Request $request, ?string $etag): bool
    {
        if (!is_string($etag)) {
            return false;
        }

        $header = $request->headers->get('If-None-Match');
        if (!is_string($header) || trim($header) === '') {
            return false;
        }

        if (trim($header) === '*') {
            return true;
        }

        foreach (explode(',', $header) as $candidate) {
            if ($this->normalizeTag($candidate) === $this->normalizeTag($etag)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeTag(string $tag): string
    {
        $normalized = trim($tag);
        if (str_starts_with($normalized, 'W/')) {
            $normalized = substr($normalized, 2);
        }

        return trim($normalized, " \t\n\r\0\x0B\"");
    }
}
