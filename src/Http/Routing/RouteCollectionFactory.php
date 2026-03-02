<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteCollectionFactory
{
    public function create(): RouteCollection
    {
        $routes = new RouteCollection();
        $routes->add('home', new Route('/', ['_controller' => 'home'], methods: ['GET']));
        $routes->add('contact', new Route('/contact', ['_controller' => 'contact'], methods: ['GET', 'POST']));
        $routes->add('preferences', new Route('/preferences', ['_controller' => 'preferences'], methods: ['GET', 'POST']));

        return $routes;
    }
}
