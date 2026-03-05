<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\AppEnvironment;
use Showoff\Core\Config\DatabaseConfig;
use Showoff\Core\Domain\Clock\Clock;
use Showoff\Core\Domain\Contact\ContactSubmission;
use Showoff\Core\Domain\Contact\ContactSubmissionEvent;
use Showoff\Core\Domain\Contact\ContactSubmissionId;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionEventRepository;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionRepository;
use Showoff\Core\Domain\Contact\SubmitContactSubmission;
use Showoff\Core\Domain\Shared\TransactionBoundary;
use Showoff\Core\Http\Contact\HeaderSubmissionSourceStrategy;
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
            database: new DatabaseConfig('mysql', null, 'db', 3306, 'showoff', 'showoff', 'showoff', 'utf8mb4'),
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
        $submissionRepository = new InMemoryContactSubmissionRepository();
        $eventRepository = new InMemoryContactSubmissionEventRepository();
        $resolver = new ControllerResolver([
            'home' => new HomeController($appConfig, $renderer, $sessionManager),
            'contact' => new ContactController(
                $appConfig,
                $renderer,
                $sessionManager,
                new ContactFormHandler($tokenManager),
                $tokenManager,
                new HeaderSubmissionSourceStrategy(),
                new SubmitContactSubmission(
                    new ImmediateTransactionManager(),
                    $submissionRepository,
                    $eventRepository,
                    new FixedClock(new \DateTimeImmutable('2026-03-02 10:00:00')),
                ),
                $submissionRepository,
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

final class InMemoryContactSubmissionRepository implements ContactSubmissionRepository
{
    /** @var list<ContactSubmission> */
    private array $submissions = [];

    public function save(ContactSubmission $submission): ContactSubmission
    {
        $stored = $submission->withId(new ContactSubmissionId(count($this->submissions) + 1));
        $this->submissions[] = $stored;

        return $stored;
    }

    public function countAll(): int
    {
        return count($this->submissions);
    }

    public function latest(): ?ContactSubmission
    {
        if ($this->submissions === []) {
            return null;
        }

        return $this->submissions[count($this->submissions) - 1];
    }
}

final class InMemoryContactSubmissionEventRepository implements ContactSubmissionEventRepository
{
    /** @var list<ContactSubmissionEvent> */
    private array $events = [];

    public function save(ContactSubmissionEvent $event): ContactSubmissionEvent
    {
        $stored = new ContactSubmissionEvent(
            id: count($this->events) + 1,
            submissionId: $event->submissionId,
            name: $event->name,
            occurredAt: $event->occurredAt,
            metadata: $event->metadata,
        );
        $this->events[] = $stored;

        return $stored;
    }

    public function countForSubmission(ContactSubmissionId $submissionId): int
    {
        return count(array_filter(
            $this->events,
            static fn(ContactSubmissionEvent $event): bool => $event->submissionId->value === $submissionId->value,
        ));
    }
}

final readonly class ImmediateTransactionManager implements TransactionBoundary
{
    public function transactional(callable $operation): mixed
    {
        return $operation();
    }
}

final readonly class FixedClock implements Clock
{
    public function __construct(
        private \DateTimeImmutable $now,
    ) {}

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
