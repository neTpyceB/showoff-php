<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Form;

final readonly class ContactFormData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $message,
    ) {}
}
