<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Functional;

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

final class SecurityArchitectureTest extends WebTestCase
{
    protected function setUp(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $container = $client->getContainer();
        $this->runMigrations($container);
        $this->seedUsers($container);
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

    public function testAdminRouteRequiresAuthentication(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/login', 303);
    }

    public function testLoginAuthenticatesAndGrantsAdminAccess(): void
    {
        $client = self::requireClient();
        $token = $this->csrfToken($client, '/login');
        $client->request('POST', '/login', [
            '_csrf_token' => $token,
            'email' => 'admin@example.com',
            'password' => 'VeryStrongPassword123!',
        ]);

        self::assertResponseRedirects('/admin', 303);

        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Admin panel');
    }

    public function testInvalidLoginIsRejected(): void
    {
        $client = self::requireClient();
        $token = $this->csrfToken($client, '/login');
        $client->request('POST', '/login', [
            '_csrf_token' => $token,
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        self::assertResponseStatusCodeSame(401);
        self::assertSelectorTextContains('.error', 'Invalid credentials.');
    }

    public function testLoginRequiresValidCsrfToken(): void
    {
        $client = self::requireClient();
        $client->request('POST', '/login', [
            '_csrf_token' => 'invalid',
            'email' => 'admin@example.com',
            'password' => 'VeryStrongPassword123!',
        ]);

        self::assertResponseStatusCodeSame(403);
        self::assertSelectorTextContains('.error', 'Invalid form token. Refresh and try again.');
    }

    public function testLoginRateLimitBlocksRepeatedFailures(): void
    {
        $client = self::requireClient();
        $email = 'blocked+' . bin2hex(random_bytes(4)) . '@example.com';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $token = $this->csrfToken($client, '/login');
            $client->request('POST', '/login', [
                '_csrf_token' => $token,
                'email' => $email,
                'password' => 'wrong-password',
            ]);
        }

        self::assertResponseStatusCodeSame(429);
        self::assertSelectorTextContains('.error', 'Too many failed sign-in attempts. Try again later.');
        self::assertTrue($client->getResponse()->headers->has('Retry-After'));
    }

    public function testSecurityHeadersAreApplied(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSame('nosniff', $client->getResponse()->headers->get('X-Content-Type-Options'));
        self::assertSame('DENY', $client->getResponse()->headers->get('X-Frame-Options'));
        self::assertSame('no-referrer', $client->getResponse()->headers->get('Referrer-Policy'));
        self::assertNotNull($client->getResponse()->headers->get('Content-Security-Policy'));
    }

    public function testLogoutRequiresValidCsrfToken(): void
    {
        $client = self::requireClient();
        $token = $this->csrfToken($client, '/login');
        $client->request('POST', '/login', [
            '_csrf_token' => $token,
            'email' => 'admin@example.com',
            'password' => 'VeryStrongPassword123!',
        ]);
        self::assertResponseRedirects('/admin', 303);

        $client->request('POST', '/logout', ['_csrf_token' => 'invalid']);

        self::assertResponseStatusCodeSame(403);
    }

    public function testProtectedApiWriteRequiresBearerToken(): void
    {
        $client = self::requireClient();
        $client->request(
            'POST',
            '/api/v1/contact-submissions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'No Token',
                'email' => 'none@example.com',
                'message' => 'Unauthorized request should be rejected.',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiTokenIssuanceIsRateLimitedOnFailures(): void
    {
        $client = self::requireClient();
        $email = 'api-lock+' . bin2hex(random_bytes(4)) . '@example.com';

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $client->request(
                'POST',
                '/api/v1/auth/token',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: json_encode([
                    'email' => $email,
                    'password' => 'wrong-password',
                ], JSON_THROW_ON_ERROR),
            );
        }

        self::assertResponseStatusCodeSame(429);
        self::assertResponseFormatSame('json');
        self::assertTrue($client->getResponse()->headers->has('Retry-After'));
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

    private function seedUsers(ContainerInterface $container): void
    {
        $users = $container->get(UserRepository::class);
        $hasher = $container->get(PasswordHasher::class);
        if (!$users instanceof UserRepository || !$hasher instanceof PasswordHasher) {
            throw new \RuntimeException('Security services unavailable.');
        }

        if ($users->findByEmail('admin@example.com') === null) {
            $users->create(
                'admin@example.com',
                $hasher->hash('VeryStrongPassword123!'),
                Role::ADMIN,
            );
        }
    }

    private static function requireClient(): KernelBrowser
    {
        $client = parent::getClient();
        if (!$client instanceof KernelBrowser) {
            throw new \RuntimeException('KernelBrowser is not initialized.');
        }

        return $client;
    }

    private function csrfToken(KernelBrowser $client, string $path): string
    {
        $crawler = $client->request('GET', $path);
        $input = $crawler->filter('input[name="_csrf_token"]')->first();
        self::assertGreaterThan(0, $input->count());

        $token = $input->attr('value');
        self::assertIsString($token);

        return $token;
    }
}
