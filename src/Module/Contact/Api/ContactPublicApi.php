<?php

declare(strict_types=1);

namespace App\Module\Contact\Api;

interface ContactPublicApi
{
    public function stats(): ContactSubmissionStatsView;

    public function submit(ContactSubmissionInput $input): ContactSubmissionView;
}
