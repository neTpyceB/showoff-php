<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Form;

final readonly class PreferencesFormResult
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        public bool $isValid,
        public string $theme,
        public array $errors,
    ) {}
}
