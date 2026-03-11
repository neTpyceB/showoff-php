<?php

declare(strict_types=1);

namespace App\Realtime\Publisher;

final readonly class MercureRealtimePublisher implements RealtimePublisher
{
    public function __construct(
        private string $hubUrl,
        private string $jwt,
        private int $timeoutMs,
    ) {}

    public function publish(string $topic, array $payload): void
    {
        $hubUrl = trim($this->hubUrl);
        if ($hubUrl === '') {
            return;
        }

        try {
            $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return;
        }

        $headers = ['Content-Type: application/x-www-form-urlencoded'];
        if ($this->jwt !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->jwt;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => http_build_query([
                    'topic' => $topic,
                    'data' => $jsonPayload,
                ], '', '&', PHP_QUERY_RFC3986),
                'timeout' => max(0.1, $this->timeoutMs / 1000),
                'ignore_errors' => true,
            ],
        ]);

        set_error_handler(static fn(): bool => true);

        try {
            file_get_contents($hubUrl, false, $context);
        } catch (\Throwable) {
        } finally {
            restore_error_handler();
        }
    }
}
