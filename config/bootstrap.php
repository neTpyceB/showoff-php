<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$projectRoot = dirname(__DIR__);

require $projectRoot . '/vendor/autoload.php';

if (file_exists($projectRoot . '/.env')) {
    new Dotenv()->bootEnv($projectRoot . '/.env');
}
