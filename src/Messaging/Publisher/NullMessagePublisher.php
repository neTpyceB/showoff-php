<?php

declare(strict_types=1);

namespace App\Messaging\Publisher;

final class NullMessagePublisher implements MessagePublisher
{
    /**
     * @var list<string>
     */
    private array $published = [];

    public function publish(string $payload): void
    {
        $this->published[] = $payload;
    }

    /**
     * @return list<string>
     */
    public function published(): array
    {
        return $this->published;
    }
}
