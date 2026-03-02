<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Session;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class WebSessionManager
{
    private const string FLASH_KEY = '_app_flashes';

    private ?SessionInterface $sharedSession = null;

    public function __construct(
        private readonly SessionFactory $sessionFactory,
    ) {}

    public function start(Request $request): SessionInterface
    {
        if ($request->hasSession()) {
            $session = $request->getSession();
        } else {
            $session = $this->sharedSession ??= $this->sessionFactory->create();
            $request->setSession($session);
        }

        if (!$session->isStarted()) {
            $session->start();
        }

        return $session;
    }

    public function finalize(Request $request, Response $response): Response
    {
        if (!$request->hasSession()) {
            return $response;
        }

        $session = $request->getSession();

        if (!$session->isStarted()) {
            return $response;
        }

        $session->save();

        return $response;
    }

    public function addFlash(Request $request, string $type, string $message): void
    {
        $session = $this->start($request);
        $flashes = $this->flashes($session);
        $flashes[$type] ??= [];
        $flashes[$type][] = $message;
        $session->set(self::FLASH_KEY, $flashes);
    }

    /**
     * @return array<string, list<string>>
     */
    public function consumeFlashes(Request $request): array
    {
        $session = $this->start($request);
        $flashes = $this->flashes($session);
        $session->remove(self::FLASH_KEY);

        return $flashes;
    }

    public function increment(Request $request, string $key, int $step = 1): int
    {
        $session = $this->start($request);
        $value = $this->getInt($request, $key) + $step;
        $session->set($key, $value);

        return $value;
    }

    public function getInt(Request $request, string $key, int $default = 0): int
    {
        $value = $this->start($request)->get($key, $default);

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    /**
     * @return array<string, list<string>>
     */
    private function flashes(SessionInterface $session): array
    {
        $flashes = $session->get(self::FLASH_KEY, []);

        if (!is_array($flashes)) {
            return [];
        }

        $normalized = [];

        foreach ($flashes as $type => $messages) {
            if (!is_string($type) || !is_array($messages)) {
                continue;
            }

            $normalized[$type] = array_values(array_filter(
                $messages,
                static fn(mixed $message): bool => is_string($message),
            ));
        }

        return $normalized;
    }
}
