<?php

declare(strict_types=1);

namespace Showoff\Core\Bootstrap\Factory;

use Showoff\Core\Http\Controller\ContactController;
use Showoff\Core\Http\Controller\ControllerResolver;
use Showoff\Core\Http\Controller\HomeController;
use Showoff\Core\Http\Controller\PreferencesController;
use Showoff\Core\Http\HttpKernel;
use Showoff\Core\Http\Routing\RouteCollectionFactory;
use Showoff\Core\Http\Session\WebSessionManager;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

final readonly class HttpKernelFactory
{
    public function __construct(
        private HomeController $homeController,
        private ContactController $contactController,
        private PreferencesController $preferencesController,
        private WebSessionManager $sessionManager,
    ) {}

    public function create(): HttpKernel
    {
        $routeCollection = new RouteCollectionFactory()->create();
        $resolver = new ControllerResolver([
            'home' => $this->homeController,
            'contact' => $this->contactController,
            'preferences' => $this->preferencesController,
        ]);

        return new HttpKernel(
            new UrlMatcher($routeCollection, new RequestContext()),
            $resolver,
            $this->sessionManager,
        );
    }
}
