<?php

declare(strict_types=1);

namespace Showoff\Core\Http;

use Showoff\Core\Http\Controller\ControllerResolver;
use Showoff\Core\Http\Session\WebSessionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

final readonly class HttpKernel
{
    public function __construct(
        private UrlMatcher $urlMatcher,
        private ControllerResolver $controllerResolver,
        private WebSessionManager $sessionManager,
    ) {}

    public function handle(Request $request): Response
    {
        $this->urlMatcher->setContext(new RequestContext()->fromRequest($request));
        $this->sessionManager->start($request);

        try {
            /** @var array<string, mixed> $attributes */
            $attributes = $this->urlMatcher->match($request->getPathInfo());
            $request->attributes->add($attributes);

            $controllerName = $attributes['_controller'] ?? null;

            if (!is_string($controllerName)) {
                throw new ResourceNotFoundException();
            }

            $controller = $this->controllerResolver->resolve($controllerName);
            $response = $controller($request);
        } catch (ResourceNotFoundException) {
            $response = new Response('Not Found', Response::HTTP_NOT_FOUND);
        } catch (MethodNotAllowedException $exception) {
            $response = new Response('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED, [
                'Allow' => implode(', ', $exception->getAllowedMethods()),
            ]);
        }

        return $this->sessionManager->finalize($request, $response);
    }
}
