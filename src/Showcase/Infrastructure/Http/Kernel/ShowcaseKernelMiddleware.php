<?php

declare(strict_types=1);

namespace App\Showcase\Infrastructure\Http\Kernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final readonly class ShowcaseKernelMiddleware implements HttpKernelInterface
{
    public function __construct(
        private HttpKernelInterface $inner,
    ) {}

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        if ($type === self::MAIN_REQUEST) {
            $request->attributes->set('_showcase.middleware', true);
        }

        $response = $this->inner->handle($request, $type, $catch);
        if ($type === self::MAIN_REQUEST) {
            $response->headers->set('X-Showcase-Middleware', 'active');
        }

        return $response;
    }
}
