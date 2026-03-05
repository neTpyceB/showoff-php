<?php

declare(strict_types=1);

namespace Showoff\Core\Http\Contact;

use Symfony\Component\HttpFoundation\Request;

final readonly class HeaderSubmissionSourceStrategy implements SubmissionSourceStrategy
{
    public function resolve(Request $request): string
    {
        $source = trim((string) $request->headers->get('X-Submission-Source', ''));

        if ($source === '') {
            return 'web_contact';
        }

        if (preg_match('/^[a-z0-9:_-]{3,64}$/', strtolower($source)) !== 1) {
            return 'web_contact';
        }

        return strtolower($source);
    }
}
