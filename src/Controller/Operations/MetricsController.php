<?php

declare(strict_types=1);

namespace App\Controller\Operations;

use App\Observability\Metrics\RequestMetricsStore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class MetricsController
{
    public function __construct(
        private RequestMetricsStore $metrics,
        private string $metricsToken,
    ) {}

    #[Route('/metrics', name: 'ops_metrics', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        if ($this->metricsToken !== '') {
            $provided = $request->headers->get('X-Metrics-Token');
            if (!is_string($provided) || !hash_equals($this->metricsToken, trim($provided))) {
                return new Response('Forbidden', Response::HTTP_FORBIDDEN);
            }
        }

        return new Response(
            $this->metrics->toPrometheus(),
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8'],
        );
    }
}
