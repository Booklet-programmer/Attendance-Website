<?php
declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$base = dirname(__DIR__);
if (file_exists($base . '/.env')) {
    Dotenv::createImmutable($base)->safeLoad();
}
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Africa/Accra');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/session_handler.php';

session_name($_ENV['SESSION_NAME'] ?? 'upsa_attendance');
session_set_cookie_params([
    'httponly' => true,
    'secure' => (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_ENV['APP_ENV'] ?? '') === 'production')
    ),
    'samesite' => 'Lax',
    'path' => '/'
]);

// Vercel container instances are stateless; persist PHP sessions in PostgreSQL.
session_set_save_handler(new PostgresSessionHandler(), true);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
