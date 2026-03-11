<?php

declare(strict_types=1);

namespace App\Module\Analytics\Api;

interface AnalyticsPublicApi
{
    public function contactSubmissionProcessing(): ContactSubmissionProcessingView;
}
