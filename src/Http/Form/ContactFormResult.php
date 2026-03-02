<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Form;

final readonly class ContactFormResult
{
    /**
     * @param array<string, string> $errors
     * @param array{name: string, email: string, message: string} $submittedValues
     */
    public function __construct(
        public bool $isValid,
        public ?ContactFormData $data,
        public array $errors,
        public array $submittedValues,
    ) {}
}
