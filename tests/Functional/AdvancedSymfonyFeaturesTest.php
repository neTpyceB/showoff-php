<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Functional;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdvancedSymfonyFeaturesTest extends WebTestCase
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

    public function testReportEndpointUsesSerializerNormalizerAndKernelHooks(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/api/v1/showcase/report');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');
        self::assertResponseHeaderSame('X-Showcase-Module', 'advanced_symfony_showcase');
        self::assertResponseHeaderSame('X-Showcase-Middleware', 'active');
        self::assertTrue($client->getResponse()->headers->has('X-Showcase-Trace'));

        $payload = $this->decodeResponse($client);
        self::assertSame('advanced_symfony_showcase', $payload['module'] ?? null);
        self::assertSame(\App\Showcase\Infrastructure\Serialization\ShowcaseReportNormalizer::class, $payload['normalizedBy'] ?? null);
    }

    public function testDiagnosticsEndpointIsProtectedByVoter(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/api/v1/showcase/diagnostics');
        self::assertResponseStatusCodeSame(403);

        $client->request('GET', '/api/v1/showcase/diagnostics', server: [
            'HTTP_X_SHOWCASE_ROLES' => 'ROLE_ADMIN',
        ]);
        self::assertResponseIsSuccessful();
    }

    public function testFormValidationEndpointRejectsInvalidCode(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/v1/showcase/settings/validate',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'code' => 'Invalid-Code',
                'notes' => 'invalid due uppercase',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(422);
        $payload = $this->decodeResponse($client);
        self::assertFalse((bool) ($payload['valid'] ?? true));
    }

    public function testFormValidationEndpointAcceptsValidCode(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/v1/showcase/settings/validate',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'code' => 'valid-code-101',
                'notes' => 'all good',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $payload = $this->decodeResponse($client);
        self::assertTrue((bool) ($payload['valid'] ?? false));
        $data = $payload['data'] ?? [];
        self::assertIsArray($data);
        self::assertSame('valid-code-101', $data['code'] ?? null);
    }

    public function testAuditEndpointDispatchesMessengerMessage(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/v1/showcase/audit',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'action' => 'pipeline.started',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $payload = $this->decodeResponse($client);
        self::assertSame('accepted', $payload['status'] ?? null);
        self::assertIsInt($payload['messagesTotal'] ?? null);
        self::assertGreaterThanOrEqual(1, $payload['messagesTotal']);
        self::assertSame('pipeline.started', $payload['lastAction'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(KernelBrowser $client): array
    {
        $decoded = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            self::fail('Response payload must be a JSON object.');
        }

        $normalized = [];
        foreach ($decoded as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private static function requireClient(): KernelBrowser
    {
        $client = static::createClient();
        $client->disableReboot();

        return $client;
    }
}
