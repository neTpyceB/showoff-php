<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Controller;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class ControllerResolver
{
    /**
     * @param array<string, callable(Request): Response> $controllers
     */
    public function __construct(
        private array $controllers,
    ) {}

    /**
     * @return callable(Request): Response
     */
    public function resolve(string $controller): callable
    {
        return $this->controllers[$controller]
            ?? throw new InvalidArgumentException(sprintf('Unknown controller "%s".', $controller));
    }
}
