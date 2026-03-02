<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$factory = new Showoff\Core\Bootstrap\HttpApplicationFactory(dirname(__DIR__));
$response = $factory->create()->handle(Symfony\Component\HttpFoundation\Request::createFromGlobals());
$response->send();
