<?php
/**
 * Application bootstrap.
 * Included at the top of every public/admin/api entry point.
 */

declare(strict_types=1);

error_reporting(E_ALL);
// Never display raw errors to customers in production.
ini_set('display_errors', getenv('APP_DEBUG') === 'true' ? '1' : '0');
ini_set('log_errors', '1');
// Best-effort log dir; skip when the filesystem is read-only (serverless).
$logDir = __DIR__ . '/../storage/logs';
if (@is_dir($logDir) || @mkdir($logDir, 0777, true)) {
    ini_set('error_log', $logDir . '/php-error.log');
}

// ---- Load .env (tiny parser, no dependency required) ----
$envPath = __DIR__ . '/../.env';
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

define('APP_NAME', getenv('APP_NAME') ?: 'Food Factory');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_ENV', getenv('APP_ENV') ?: 'production');

// ---- DB must be loaded before sessions: on serverless hosts sessions are
//      stored in the database so they survive cold starts. ----
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/session-handler.php';

// ---- Secure session configuration ----
if (session_status() === PHP_SESSION_NONE) {
    $secure = getenv('APP_ENV') === 'production'
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('ff_session');

    // DB-backed sessions (read/write from the `sessions` table).
    session_set_save_handler(new DatabaseSessionHandler(), true);
    session_start();

    // Basic session fixation protection.
    if (empty($_SESSION['_initiated'])) {
        session_regenerate_id(true);
        $_SESSION['_initiated'] = true;
    }
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cart.php';
