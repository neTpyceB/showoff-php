<?php

declare(strict_types=1);

namespace Showoff\Core\Config;

enum AppEnvironment: string
{
    case Local = 'local';
    case Test = 'test';
    case Staging = 'staging';
    case Production = 'production';

    public static function fromString(string $value): self
    {
        return self::tryFrom(strtolower($value))
            ?? throw new ConfigurationException(sprintf('Unsupported APP_ENV value "%s".', $value));
    }

    public function isProduction(): bool
    {
        return $this === self::Production;
    }
}
