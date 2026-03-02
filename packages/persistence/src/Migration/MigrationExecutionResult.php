<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Migration;

final readonly class MigrationExecutionResult
{
    /**
     * @param list<string> $appliedVersions
     * @param list<string> $skippedVersions
     */
    public function __construct(
        public array $appliedVersions,
        public array $skippedVersions,
    ) {}
}
