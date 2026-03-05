<?php

declare(strict_types=1);

namespace Showoff\Core\Bootstrap;

use Showoff\Core\Config\ConfigLoader;
use Showoff\Core\Config\EnvironmentReader;
use Showoff\Core\Domain\Contact\SubmitContactSubmission;
use Showoff\Core\Http\Controller\ContactController;
use Showoff\Core\Http\Controller\ControllerResolver;
use Showoff\Core\Http\Controller\HomeController;
use Showoff\Core\Http\Controller\PreferencesController;
use Showoff\Core\Http\Form\ContactFormHandler;
use Showoff\Core\Http\Form\FormTokenManager;
use Showoff\Core\Http\Form\PreferencesFormHandler;
use Showoff\Core\Http\HttpKernel;
use Showoff\Core\Http\Routing\RouteCollectionFactory;
use Showoff\Core\Http\Session\NativeSessionFactory;
use Showoff\Core\Http\Session\WebSessionManager;
use Showoff\Core\Http\View\TwigViewRenderer;
use Showoff\Core\Persistence\Clock\SystemClock;
use Showoff\Core\Persistence\Connection\PdoConnectionFactory;
use Showoff\Core\Persistence\Connection\PdoTransactionManager;
use Showoff\Core\Persistence\Contact\PdoContactSubmissionEventRepository;
use Showoff\Core\Persistence\Contact\PdoContactSubmissionRepository;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final readonly class HttpApplicationFactory
{
    public function __construct(
        private string $projectRoot,
    ) {}

    public function create(): HttpKernel
    {
        require_once $this->projectRoot . '/config/bootstrap.php';

        $config = new ConfigLoader($this->projectRoot)->load(EnvironmentReader::fromGlobals());
        date_default_timezone_set($config->timezone);
        $connection = new PdoConnectionFactory()->create($config->database);
        $submissionRepository = new PdoContactSubmissionRepository($connection);
        $submissionService = new SubmitContactSubmission(
            transactionBoundary: new PdoTransactionManager($connection),
            submissionRepository: $submissionRepository,
            eventRepository: new PdoContactSubmissionEventRepository($connection),
            clock: new SystemClock(),
        );

        $renderer = new TwigViewRenderer(new Environment(
            new FilesystemLoader($this->projectRoot . '/templates'),
            [
                'cache' => false,
                'strict_variables' => true,
            ],
        ));
        $sessionManager = new WebSessionManager(new NativeSessionFactory($config));
        $tokenManager = new FormTokenManager();
        $routeCollection = new RouteCollectionFactory()->create();
        $matcher = new UrlMatcher($routeCollection, new \Symfony\Component\Routing\RequestContext());
        $resolver = new ControllerResolver([
            'home' => new HomeController($config, $renderer, $sessionManager),
            'contact' => new ContactController(
                $config,
                $renderer,
                $sessionManager,
                new ContactFormHandler($tokenManager),
                $tokenManager,
                $submissionService,
                $submissionRepository,
            ),
            'preferences' => new PreferencesController(
                $config,
                $renderer,
                $sessionManager,
                new PreferencesFormHandler($tokenManager),
                $tokenManager,
            ),
        ]);

        return new HttpKernel($matcher, $resolver, $sessionManager);
    }
}
