<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Functional;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EnterpriseArchitectureTest extends WebTestCase
{
    /**
     * @param array{environment?: string, debug?: bool} $options
     */
    protected static function createKernel(array $options = []): Kernel
    {
        $environment = isset($options['environment']) ? (string) $options['environment'] : 'test';
        $debug = isset($options['debug']) ? (bool) $options['debug'] : false;

        return new Kernel($environment, $debug);
    }

    public function testAnalyticsApiBoundaryReturnsProcessingProjection(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/api/v1/analytics/contact-submissions');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');

        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);
        self::assertIsArray($payload['data'] ?? null);
        self::assertIsInt($payload['data']['processed'] ?? null);
        self::assertArrayHasKey('lastEmail', $payload['data']);
        self::assertArrayHasKey('lastOccurredAt', $payload['data']);
    }

    public function testGraphqlExposesProcessingProjectionField(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/graphql',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'query' => '{ contactSubmissionProcessing { processed lastEmail lastOccurredAt } }',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');

        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);
        self::assertIsArray($payload['data'] ?? null);
        self::assertIsArray($payload['data']['contactSubmissionProcessing'] ?? null);
        self::assertIsInt($payload['data']['contactSubmissionProcessing']['processed'] ?? null);
    }

    private static function requireClient(): KernelBrowser
    {
        $client = static::createClient();
        $client->disableReboot();

        return $client;
    }
}
