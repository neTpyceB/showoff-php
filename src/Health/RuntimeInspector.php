<?php

declare(strict_types=1);

namespace Showoff\Core\Health;

interface RuntimeInspector
{
    public function phpVersion(): string;

    public function isExtensionLoaded(string $extension): bool;
}
