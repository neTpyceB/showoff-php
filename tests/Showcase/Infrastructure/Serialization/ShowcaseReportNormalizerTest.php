<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Showcase\Infrastructure\Serialization;

use App\Showcase\Application\Processor\ShowcaseProcessorResult;
use App\Showcase\Application\Report\ShowcaseReport;
use App\Showcase\Infrastructure\Serialization\ShowcaseReportNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShowcaseReportNormalizer::class)]
final class ShowcaseReportNormalizerTest extends TestCase
{
    public function testItNormalizesReportWithMetadata(): void
    {
        $normalizer = new ShowcaseReportNormalizer();
        $report = new ShowcaseReport(
            module: 'advanced_symfony_showcase',
            generatedAt: '2026-03-11T12:00:00+00:00',
            results: [
                new ShowcaseProcessorResult('diagnostics', ['status' => 'ok']),
            ],
        );

        $normalized = $normalizer->normalize($report, 'json');

        self::assertSame('advanced_symfony_showcase', $normalized['module'] ?? null);
        self::assertSame(1, $normalized['resultCount'] ?? null);
        self::assertSame(ShowcaseReportNormalizer::class, $normalized['normalizedBy'] ?? null);
    }
}
