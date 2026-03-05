<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Functional;

use App\Kernel;
use Showoff\Core\Persistence\Migration\PdoMigrator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class SymfonyMvcFoundationTest extends WebTestCase
{
    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->runMigrations($container);
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
        $migrator = $container->get(PdoMigrator::class);
        if (!$migrator instanceof PdoMigrator) {
            throw new \RuntimeException('Expected PdoMigrator service.');
        }

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
