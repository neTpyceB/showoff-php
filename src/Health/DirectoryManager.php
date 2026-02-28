<?php

declare(strict_types=1);

namespace Showoff\Core\Health;

interface DirectoryManager
{
    public function ensureWritable(string $path): bool;
}
