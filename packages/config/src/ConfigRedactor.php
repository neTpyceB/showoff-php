<?php

declare(strict_types=1);

namespace Showoff\Core\Config;

final class ConfigRedactor
{
    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function redact(array $config): array
    {
        $redacted = [];

        foreach ($config as $key => $value) {
            if (is_array($value)) {
                /** @var array<string, mixed> $nestedValue */
                $nestedValue = $value;
                $redacted[$key] = $this->redact($nestedValue);

                continue;
            }

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
