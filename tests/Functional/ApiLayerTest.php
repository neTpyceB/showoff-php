<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Functional;

use App\Kernel;
use Showoff\Core\Persistence\Migration\PdoMigrator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ApiLayerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $client = static::createClient();
        $this->runMigrations($client->getContainer());
    }

    /**
     * @param array{environment?: string, debug?: bool} $options
     */
    protected static function createKernel(array $options = []): Kernel
    {
        $environment = isset($options['environment']) ? (string) $options['environment'] : 'test';
        $debug = isset($options['debug']) ? (bool) $options['debug'] : true;

        return new Kernel($environment, $debug);
    }

    public function testRestIndexReturnsStatsPayload(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/api/v1/contact-submissions');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');

        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $root = $this->assertJsonObject($payload);
        $data = $this->assertArrayValue($root, 'data');
        self::assertSame(0, $this->assertIntValue($data, 'count'));
        self::assertNull($data['latest'] ?? null);
    }

    public function testRestStoreValidatesPayload(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/v1/contact-submissions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => '',
                'email' => 'invalid',
                'message' => 'short',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(422);
        self::assertResponseFormatSame('json');
    }

    public function testRestStoreCreatesSubmission(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/v1/contact-submissions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Ada Lovelace',
                'email' => 'ada@example.com',
                'message' => 'This is a production-quality REST test payload.',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        self::assertResponseFormatSame('json');

        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $root = $this->assertJsonObject($payload);
        $data = $this->assertArrayValue($root, 'data');
        self::assertSame('Ada Lovelace', $this->assertStringValue($data, 'name'));
        self::assertSame('ada@example.com', $this->assertStringValue($data, 'email'));
        self::assertSame('received', $this->assertStringValue($data, 'status'));
    }

    public function testGraphqlQueryReturnsStatsPayload(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/graphql',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'query' => '{ contactSubmissionStats { count latest { id email } } }',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');

        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $root = $this->assertJsonObject($payload);
        $data = $this->assertArrayValue($root, 'data');
        $stats = $this->assertArrayValue($data, 'contactSubmissionStats');
        self::assertSame(0, $this->assertIntValue($stats, 'count'));
    }

    public function testGraphqlMutationCreatesSubmission(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/graphql',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'query' => 'mutation Submit($input: SubmitContactSubmissionInput!) { submitContactSubmission(input: $input) { submission { id name email status } } }',
                'variables' => [
                    'input' => [
                        'name' => 'Grace Hopper',
                        'email' => 'grace@example.com',
                        'message' => 'GraphQL mutation payload for foundational API stage.',
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');

        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $root = $this->assertJsonObject($payload);
        $data = $this->assertArrayValue($root, 'data');
        $mutation = $this->assertArrayValue($data, 'submitContactSubmission');
        $submission = $this->assertArrayValue($mutation, 'submission');
        self::assertSame('Grace Hopper', $this->assertStringValue($submission, 'name'));
        self::assertSame('received', $this->assertStringValue($submission, 'status'));
    }

    private function runMigrations(ContainerInterface $container): void
    {
        $migrator = $container->get(PdoMigrator::class);
        if (!$migrator instanceof PdoMigrator) {
            throw new \RuntimeException('Expected PdoMigrator service.');
        }

        $migrator->migrate();
    }

    private static function requireClient(): KernelBrowser
    {
        $client = parent::getClient();
        if (!$client instanceof KernelBrowser) {
            throw new \RuntimeException('KernelBrowser is not initialized.');
        }

        return $client;
    }

    /**
     * @param mixed $payload
     *
     * @return array<string, mixed>
     */
    private function assertJsonObject(mixed $payload): array
    {
        self::assertIsArray($payload);

        $normalized = [];
        foreach ($payload as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function assertArrayValue(array $payload, string $key): array
    {
        $value = $payload[$key] ?? null;
        self::assertIsArray($value);

        return $this->assertJsonObject($value);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertStringValue(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;
        self::assertIsString($value);

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertIntValue(array $payload, string $key): int
    {
        $value = $payload[$key] ?? null;
        self::assertIsInt($value);

        return $value;
    }
}
