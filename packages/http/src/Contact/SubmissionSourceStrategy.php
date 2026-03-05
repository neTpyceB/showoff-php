<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Contact;

use Symfony\Component\HttpFoundation\Request;

interface SubmissionSourceStrategy
{
    public function resolve(Request $request): string;
}
