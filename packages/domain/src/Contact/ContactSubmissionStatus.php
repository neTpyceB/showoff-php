<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact;

enum ContactSubmissionStatus: string
{
    case Received = 'received';
}
