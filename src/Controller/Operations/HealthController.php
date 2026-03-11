<?php

declare(strict_types=1);

namespace App\Controller\Operations;

use App\Operations\Health\ReadinessChecker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController
{
    #[Route('/health/live', name: 'ops_health_live', methods: ['GET'])]
    public function live(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'alive',
            'timestamp' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
        ]);
    }

    #[Route('/health/ready', name: 'ops_health_ready', methods: ['GET'])]
    public function ready(ReadinessChecker $readiness): JsonResponse
    {
        $report = $readiness->check();

        return new JsonResponse(
            $report,
            $report['status'] === 'ready' ? JsonResponse::HTTP_OK : JsonResponse::HTTP_SERVICE_UNAVAILABLE,
        );
    }
}
