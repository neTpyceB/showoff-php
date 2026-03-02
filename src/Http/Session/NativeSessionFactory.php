<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Session;

use Showoff\Core\Config\AppConfig;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

final readonly class NativeSessionFactory implements SessionFactory
{
    public function __construct(
        private AppConfig $config,
    ) {}

    public function create(): SessionInterface
    {
        return new Session(new NativeSessionStorage([
            'name' => $this->config->sessionName,
            'cookie_httponly' => true,
            'cookie_secure' => $this->config->sessionCookieSecure,
            'cookie_samesite' => 'lax',
            'cookie_path' => '/',
            'use_strict_mode' => 1,
        ]));
    }
}
