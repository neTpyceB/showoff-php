<?php

declare(strict_types=1);

namespace App\Showcase\Infrastructure\Serialization;

use App\Showcase\Application\Processor\ShowcaseProcessorResult;
use App\Showcase\Application\Report\ShowcaseReport;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ShowcaseReportNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return [ShowcaseReport::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ShowcaseReport;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!$object instanceof ShowcaseReport) {
            return [];
        }

        return [
            'module' => $object->module,
            'generatedAt' => $object->generatedAt,
            'resultCount' => count($object->results),
            'results' => array_map(
                static fn(ShowcaseProcessorResult $result): array => $result->toArray(),
                $object->results,
            ),
            'normalizedBy' => self::class,
        ];
    }
}
