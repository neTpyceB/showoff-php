<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Functional;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SymfonyMvcFoundationTest extends WebTestCase
{
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
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'HTTP fundamentals');
    }

    public function testItValidatesContactFormInput(): void
    {
        $client = static::createClient();
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
        $client = static::createClient();
        $client->request('POST', '/preferences', [
            'theme' => 'dark',
        ]);

        self::assertResponseRedirects('/preferences', 303);
    }
}
