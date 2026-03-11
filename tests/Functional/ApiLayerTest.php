<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Functional;

use App\Concurrency\RequestLockManager;
use App\Kernel;
use App\Security\PasswordHasher;
use App\Security\Role;
use App\Security\UserRepository;
use Showoff\Core\Persistence\Migration\PdoMigrator;
use Showoff\Core\Persistence\Migration\Version202603020001;
use Showoff\Core\Persistence\Migration\Version202603100001;
use Showoff\Core\Persistence\Migration\Version202603110001;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ApiLayerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $container = $client->getContainer();
        $this->runMigrations($container);
        $this->seedApiUser($container);
    }

    /**
     * @param array{environment?: string, debug?: bool} $options
     */
    protected static function createKernel(array $options = []): Kernel
    {
        $environment = isset($options['environment']) ? (string) $options['environment'] : 'test';
        $debug = isset($options['debug']) ? (bool) $options['debug'] : false;

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
        self::assertNotNull($client->getResponse()->headers->get('ETag'));
        self::assertNotNull($client->getResponse()->headers->get('X-Response-Time-Ms'));
        self::assertNotNull($client->getResponse()->headers->get('X-Memory-Delta-Kb'));
    }

    public function testRestIndexReturnsNotModifiedWhenEtagMatches(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/api/v1/contact-submissions');
        $etag = $client->getResponse()->headers->get('ETag');
        self::assertNotNull($etag);

        $client->request('GET', '/api/v1/contact-submissions', server: [
            'HTTP_IF_NONE_MATCH' => $etag,
        ]);

        self::assertResponseStatusCodeSame(304);
    }

    public function testRestStoreValidatesPayload(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/v1/contact-submissions',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->issueAccessToken(self::requireClient()),
            ],
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
        $token = $this->issueAccessToken($client);
        $client->request(
            'POST',
            '/api/v1/contact-submissions',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
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

    public function testRestStoreReplaysByIdempotencyKey(): void
    {
        $client = self::requireClient();
        $token = $this->issueAccessToken($client);
        $idempotencyKey = 'rest-idempotency-' . bin2hex(random_bytes(6));

        $client->request(
            'POST',
            '/api/v1/contact-submissions',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'HTTP_IDEMPOTENCY_KEY' => $idempotencyKey,
            ],
            content: json_encode([
                'name' => 'Ada Lovelace',
                'email' => 'ada@example.com',
                'message' => 'This request should be stored once.',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        $firstPayload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $firstData = $this->assertArrayValue($this->assertJsonObject($firstPayload), 'data');
        $firstId = $this->assertIntValue($firstData, 'id');

        $client->request(
            'POST',
            '/api/v1/contact-submissions',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'HTTP_IDEMPOTENCY_KEY' => $idempotencyKey,
            ],
            content: json_encode([
                'name' => 'Different Name',
                'email' => 'different@example.com',
                'message' => 'Different payload should still replay first response.',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        self::assertSame('true', $client->getResponse()->headers->get('X-Idempotency-Replayed'));
        $secondPayload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $secondData = $this->assertArrayValue($this->assertJsonObject($secondPayload), 'data');
        self::assertSame($firstId, $this->assertIntValue($secondData, 'id'));
    }

    public function testRestStoreReturnsConflictWhenIdempotencyKeyIsLocked(): void
    {
        $client = self::requireClient();
        $token = $this->issueAccessToken($client);
        $idempotencyKey = 'rest-locked-' . bin2hex(random_bytes(6));
        $lockKey = 'idempotency:lock:' . hash('sha256', 'rest.contact_submission.store:' . $idempotencyKey);

        $locks = $client->getContainer()->get(RequestLockManager::class);
        if (!$locks instanceof RequestLockManager) {
            throw new \RuntimeException('Expected request lock manager service.');
        }

        self::assertTrue($locks->acquire($lockKey, 30));

        $client->request(
            'POST',
            '/api/v1/contact-submissions',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'HTTP_IDEMPOTENCY_KEY' => $idempotencyKey,
            ],
            content: json_encode([
                'name' => 'Grace Hopper',
                'email' => 'grace@example.com',
                'message' => 'This request should get a conflict while lock is held.',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(409);
        $locks->release($lockKey);
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
        self::assertNotNull($client->getResponse()->headers->get('ETag'));
    }

    public function testGraphqlQueryReturnsNotModifiedWhenEtagMatches(): void
    {
        $client = self::requireClient();
        $content = json_encode([
            'query' => '{ contactSubmissionStats { count latest { id email } } }',
        ], JSON_THROW_ON_ERROR);

        $client->request(
            'POST',
            '/api/graphql',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $content,
        );
        $etag = $client->getResponse()->headers->get('ETag');
        self::assertNotNull($etag);

        $client->request(
            'POST',
            '/api/graphql',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_IF_NONE_MATCH' => $etag,
            ],
            content: $content,
        );

        self::assertResponseStatusCodeSame(304);
    }

    public function testGraphqlMutationCreatesSubmission(): void
    {
        $client = self::requireClient();
        $token = $this->issueAccessToken($client);
        $client->request(
            'POST',
            '/api/graphql',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
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

    public function testGraphqlMutationReplaysByIdempotencyKey(): void
    {
        $client = self::requireClient();
        $token = $this->issueAccessToken($client);
        $idempotencyKey = 'graphql-idempotency-' . bin2hex(random_bytes(6));
        $query = 'mutation Submit($input: SubmitContactSubmissionInput!) { submitContactSubmission(input: $input) { submission { id name email status } } }';

        $client->request(
            'POST',
            '/api/graphql',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'HTTP_IDEMPOTENCY_KEY' => $idempotencyKey,
            ],
            content: json_encode([
                'query' => $query,
                'variables' => [
                    'input' => [
                        'name' => 'Grace Hopper',
                        'email' => 'grace@example.com',
                        'message' => 'GraphQL mutation should store once.',
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $firstPayload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $firstRoot = $this->assertJsonObject($firstPayload);
        $firstData = $this->assertArrayValue($firstRoot, 'data');
        $firstMutation = $this->assertArrayValue($firstData, 'submitContactSubmission');
        $firstSubmission = $this->assertArrayValue($firstMutation, 'submission');
        $firstId = $this->assertIntValue($firstSubmission, 'id');

        $client->request(
            'POST',
            '/api/graphql',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'HTTP_IDEMPOTENCY_KEY' => $idempotencyKey,
            ],
            content: json_encode([
                'query' => $query,
                'variables' => [
                    'input' => [
                        'name' => 'Changed User',
                        'email' => 'changed@example.com',
                        'message' => 'Second mutation should replay prior result.',
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        self::assertSame('true', $client->getResponse()->headers->get('X-Idempotency-Replayed'));
        $secondPayload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $secondRoot = $this->assertJsonObject($secondPayload);
        $secondData = $this->assertArrayValue($secondRoot, 'data');
        $secondMutation = $this->assertArrayValue($secondData, 'submitContactSubmission');
        $secondSubmission = $this->assertArrayValue($secondMutation, 'submission');
        self::assertSame($firstId, $this->assertIntValue($secondSubmission, 'id'));
    }

    private function runMigrations(ContainerInterface $container): void
    {
        $pdo = $container->get(\PDO::class);
        if (!$pdo instanceof \PDO) {
            throw new \RuntimeException('Expected PDO service.');
        }

        $migrator = new PdoMigrator($pdo, [new Version202603020001(), new Version202603100001(), new Version202603110001()]);
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

    private function issueAccessToken(KernelBrowser $client): string
    {
        $client->request(
            'POST',
            '/api/v1/auth/token',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'api@example.com',
                'password' => 'VeryStrongPassword123!',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $root = $this->assertJsonObject($payload);
        $data = $this->assertArrayValue($root, 'data');

        return $this->assertStringValue($data, 'accessToken');
    }

    private function seedApiUser(ContainerInterface $container): void
    {
        $users = $container->get(UserRepository::class);
        $hasher = $container->get(PasswordHasher::class);
        if (!$users instanceof UserRepository || !$hasher instanceof PasswordHasher) {
            throw new \RuntimeException('Security services unavailable.');
        }

        if ($users->findByEmail('api@example.com') !== null) {
            return;
        }

        $users->create(
            'api@example.com',
            $hasher->hash('VeryStrongPassword123!'),
            Role::USER,
        );
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
