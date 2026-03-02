<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Migration;

final readonly class MigrationStatus
{
    /**
     * @param list<string> $appliedVersions
     * @param list<string> $pendingVersions
     */
    public function __construct(
        public array $appliedVersions,
        public array $pendingVersions,
    ) {}
}
