<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Http\Controller\ContactController;
use Showoff\Core\Http\Controller\ControllerResolver;
use Showoff\Core\Http\Controller\HomeController;
use Showoff\Core\Http\Controller\PreferencesController;
use Showoff\Core\Http\Form\ContactFormHandler;
use Showoff\Core\Http\Form\FormTokenManager;
use Showoff\Core\Http\Form\PreferencesFormHandler;
use Showoff\Core\Http\HttpKernel;
use Showoff\Core\Http\Routing\RouteCollectionFactory;
use Showoff\Core\Http\Session\SessionFactory;
use Showoff\Core\Http\Session\WebSessionManager;
use Showoff\Core\Http\View\TwigViewRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[CoversClass(HttpKernel::class)]
final class HttpKernelTest extends TestCase
{
    public function testItRendersTheHomePage(): void
    {
        $kernel = $this->kernel();

        $response = $kernel->handle(Request::create('/'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('HTTP fundamentals', $response->getContent() ?: '');
    }

    public function testItProcessesTheContactFormWithRedirectAndCookie(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $tokenManager = new FormTokenManager();
        $token = $tokenManager->tokenFor($session, ContactFormHandler::FORM_NAME);
        $kernel = $this->kernel($session, $tokenManager);

        $request = Request::create('/contact', 'POST', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'message' => 'This message has enough content.',
            '_token' => $token,
        ]);

        $response = $kernel->handle($request);

        self::assertSame(303, $response->getStatusCode());
        self::assertSame('/contact', $response->headers->get('Location'));
        self::assertContains('last_contact_email', array_map(
            static fn($cookie) => $cookie->getName(),
            $response->headers->getCookies(),
        ));
    }

    public function testItReturnsNotFoundForUnknownRoutes(): void
    {
        $response = $this->kernel()->handle(Request::create('/missing'));

        self::assertSame(404, $response->getStatusCode());
    }

    private function kernel(?SessionInterface $session = null, ?FormTokenManager $tokenManager = null): HttpKernel
    {
        $appConfig = new AppConfig(
            appName: 'Showoff PHP Core',
            cliName: 'showoff-core',
            environment: AppEnvironment::Local,
            debug: true,
            timezone: 'UTC',
            cacheDir: '/tmp/cache',
            logLevel: 'info',
            secret: 'local-development-secret-key',
            buildCommit: null,
            appUrl: 'http://localhost:8080',
            sessionName: 'SHOWOFFSESSID',
            sessionCookieSecure: false,
        );
        $renderer = new TwigViewRenderer(new Environment(new ArrayLoader([
            'pages/home.html.twig' => 'HTTP fundamentals {{ request_count }}',
            'pages/contact.html.twig' => 'Contact {{ submission_count }}',
            'pages/preferences.html.twig' => 'Preferences {{ selected_theme }}',
        ])));
        $tokenManager ??= new FormTokenManager();
        $sessionManager = new WebSessionManager(new TestSessionFactory(
            $session ?? new Session(new MockArraySessionStorage()),
        ));
        $resolver = new ControllerResolver([
            'home' => new HomeController($appConfig, $renderer, $sessionManager),
            'contact' => new ContactController(
                $appConfig,
                $renderer,
                $sessionManager,
                new ContactFormHandler($tokenManager),
                $tokenManager,
            ),
            'preferences' => new PreferencesController(
                $appConfig,
                $renderer,
                $sessionManager,
                new PreferencesFormHandler($tokenManager),
                $tokenManager,
            ),
        ]);

        return new HttpKernel(
            new UrlMatcher(new RouteCollectionFactory()->create(), new RequestContext()),
            $resolver,
            $sessionManager,
        );
    }
}

final readonly class TestSessionFactory implements SessionFactory
{
    public function __construct(
        private SessionInterface $session,
    ) {}

    public function create(): SessionInterface
    {
        return $this->session;
    }
}
