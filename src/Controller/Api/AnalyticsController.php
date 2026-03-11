<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Module\Analytics\Api\AnalyticsPublicApi;
use App\Performance\Http\JsonHttpCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/analytics/contact-submissions')]
final class AnalyticsController extends AbstractController
{
    public function __construct(
        private readonly AnalyticsPublicApi $analyticsApi,
        private readonly JsonHttpCacheService $httpCache,
    ) {}

    #[Route('', name: 'api_analytics_contact_submissions', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        return $this->httpCache->createCacheableResponse($request, [
            'data' => $this->analyticsApi->contactSubmissionProcessing()->toArray(),
        ]);
    }
}
