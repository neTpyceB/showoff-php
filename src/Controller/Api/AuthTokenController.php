<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Security\ApiTokenService;
use App\Security\PasswordHasher;
use App\Security\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth/token', name: 'api_auth_token', methods: ['POST'])]
final class AuthTokenController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher,
        private readonly ApiTokenService $apiTokens,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload === null) {
            return $this->json(['errors' => [['message' => 'Request body must be valid JSON.']]], Response::HTTP_BAD_REQUEST);
        }

        $email = $this->stringValue($payload, 'email');
        $password = $this->stringValue($payload, 'password');
        if ($email === '' || $password === '') {
            return $this->json(['errors' => [['message' => 'email and password are required.']]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->users->findByEmail($email);
        if (!$user || !$this->hasher->verify($password, $user->passwordHash)) {
            return $this->json(['errors' => [['message' => 'Invalid credentials.']]], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->apiTokens->issueToken($user, 'api-client', new \DateInterval('PT12H'));

        return $this->json([
            'data' => [
                'accessToken' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => 43200,
                'role' => $user->role->value,
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodePayload(Request $request): ?array
    {
        try {
            $decoded = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($decoded)) {
            return null;
        }

        $normalized = [];
        foreach ($decoded as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function stringValue(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) ? trim($value) : '';
    }
}
