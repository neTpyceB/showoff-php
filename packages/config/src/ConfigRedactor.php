<?php

declare(strict_types=1);

namespace Showoff\Core\Config;

final class ConfigRedactor
{
    /**
     * @param array<string, bool|string|null> $config
     *
     * @return array<string, bool|string|null>
     */
    public function redact(array $config): array
    {
        $redacted = [];

        foreach ($config as $key => $value) {
            $redacted[$key] = $this->isSensitive($key) && is_string($value)
                ? '[REDACTED]'
                : $value;
        }

        return $redacted;
    }

    private function isSensitive(string $key): bool
    {
        return preg_match('/secret|token|password|key/i', $key) === 1;
    }
}
