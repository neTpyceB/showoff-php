<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Functional;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DevOpsDeploymentTest extends WebTestCase
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

    public function testHealthEndpointsAreAvailable(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/health/live');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');
        self::assertStringContainsString('"status":"alive"', (string) $client->getResponse()->getContent());

        $client->request('GET', '/health/ready');
        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');
        self::assertStringContainsString('"status":"ready"', (string) $client->getResponse()->getContent());
    }

    public function testMetricsEndpointExposesPrometheusFormat(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertTrue($client->getResponse()->headers->has('X-Request-Id'));

        $client->request('GET', '/metrics');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
        self::assertStringContainsString('app_http_requests_total', (string) $client->getResponse()->getContent());
    }

    private static function requireClient(): KernelBrowser
    {
        $client = static::createClient();
        $client->disableReboot();

        return $client;
    }
}
