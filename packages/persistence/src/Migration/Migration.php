<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Migration;

use PDO;

interface Migration
{
    public function version(): string;

    public function description(): string;

    public function up(PDO $connection): void;
}
