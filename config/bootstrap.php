<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$projectRoot = dirname(__DIR__);

require $projectRoot . '/vendor/autoload.php';

if (file_exists($projectRoot . '/.env')) {
    new Dotenv()->bootEnv($projectRoot . '/.env');
}

$_SERVER['APP_ENV'] ??= $_ENV['APP_ENV'] ?? 'dev';
$_SERVER['APP_DEBUG'] ??= $_ENV['APP_DEBUG'] ?? ('prod' !== $_SERVER['APP_ENV']);
