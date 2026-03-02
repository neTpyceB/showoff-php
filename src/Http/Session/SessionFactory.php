<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

interface SessionFactory
{
    public function create(): SessionInterface;
}
