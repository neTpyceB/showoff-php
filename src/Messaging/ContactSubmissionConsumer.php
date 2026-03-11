<?php

declare(strict_types=1);

namespace App\Messaging;

interface ContactSubmissionConsumer
{
    public function consume(int $limit): int;
}
