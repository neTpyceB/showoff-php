<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Functional;

use App\Kernel;
use Showoff\Core\Persistence\Migration\PdoMigrator;
use Showoff\Core\Persistence\Migration\Version202603020001;
use Showoff\Core\Persistence\Migration\Version202603100001;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class SymfonyMvcFoundationTest extends WebTestCase
{
    protected function setUp(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $container = $client->getContainer();
        $this->runMigrations($container);
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

    public function testItRendersHomeRouteThroughSymfonyKernel(): void
    {
        $client = self::requireClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'HTTP fundamentals');
    }

    public function testItValidatesContactFormInput(): void
    {
        $client = self::requireClient();
        $client->request('POST', '/contact', [
            'name' => '',
            'email' => 'invalid',
            'message' => 'short',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('.error');
    }

    public function testItAcceptsValidPreferenceSubmission(): void
    {
        $client = self::requireClient();
        $client->request('POST', '/preferences', [
            'theme' => 'dark',
        ]);

        self::assertResponseRedirects('/preferences', 303);
    }

    private function runMigrations(ContainerInterface $container): void
    {
        $pdo = $container->get(\PDO::class);
        if (!$pdo instanceof \PDO) {
            throw new \RuntimeException('Expected PDO service.');
        }

        $migrator = new PdoMigrator($pdo, [new Version202603020001(), new Version202603100001()]);
        $migrator->migrate();
    }

    private static function requireClient(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        $client = parent::getClient();
        if (!$client instanceof \Symfony\Bundle\FrameworkBundle\KernelBrowser) {
            throw new \RuntimeException('KernelBrowser is not initialized.');
        }

        return $client;
    }
}
