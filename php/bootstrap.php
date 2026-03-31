<?php
declare(strict_types=1);

require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/config/i18n.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/controllers/HomeController.php';

loadEnvFile(dirname(__DIR__) . '/.env');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}
