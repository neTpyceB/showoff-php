<?php

declare(strict_types=1);

namespace Showoff\Core\Health;

final class NativeRuntimeInspector implements RuntimeInspector
{
    public function phpVersion(): string
    {
        return PHP_VERSION;
    }

    public function isExtensionLoaded(string $extension): bool
    {
        return extension_loaded($extension);
    }
}
