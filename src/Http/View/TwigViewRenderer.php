<?php

declare(strict_types=1);

namespace Showoff\Core\Http\View;

use Twig\Environment;

final readonly class TwigViewRenderer
{
    public function __construct(
        private Environment $twig,
    ) {}

    /**
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context = []): string
    {
        return $this->twig->render($template, $context);
    }
}
