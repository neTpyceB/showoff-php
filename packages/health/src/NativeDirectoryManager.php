<?php

declare(strict_types=1);

namespace Showoff\Core\Health;

final class NativeDirectoryManager implements DirectoryManager
{
    public function ensureWritable(string $path): bool
    {
        if (!is_dir($path) && !mkdir($path, 0o775, true) && !is_dir($path)) {
            return false;
        }

        return is_writable($path);
    }
}
