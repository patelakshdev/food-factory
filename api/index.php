<?php
/**
 * Vercel front controller (vercel-php runtime).
 *
 * vercel.json routes every request to this single serverless function and
 * Vercel serves the project's static files (assets) directly. Here we map the
 * ORIGINAL request path back to a real PHP entrypoint and include it, so the
 * whole app (public pages, admin, JSON APIs) runs live against the database
 * without exceeding Vercel's per-deployment function limits.
 *
 * Paths are strictly whitelisted; anything that is not a known page/route is
 * a 404, so internal folders (config/, includes/, database/, storage/) can
 * never be downloaded.
 */

declare(strict_types=1);

$path = $_SERVER['HTTP_X_VERCEL_FORWARDED_URL'] ?? ($_SERVER['REQUEST_URI'] ?? '/');
$path = urldecode((string)parse_url($path, PHP_URL_PATH) ?: '/');
$path = '/' . ltrim($path, '/');

$root = __DIR__ . '/..';

// Clean/friendly URLs -> real entrypoint.
$friendly = [
    '/'              => 'public/index.php',
    '/home'          => 'public/index.php',
    '/about'         => 'public/about.php',
    '/menu'          => 'public/menu.php',
    '/gallery'       => 'public/gallery.php',
    '/reviews'       => 'public/review.php',
    '/review'        => 'public/review.php',
    '/reservation'   => 'public/reservation.php',
    '/contact'       => 'public/contact.php',
    '/cart'          => 'public/cart.php',
    '/checkout'      => 'public/checkout.php',
    '/login'         => 'public/login.php',
    '/register'      => 'public/register.php',
    '/logout'        => 'public/logout.php',
    '/orders'        => 'public/orders.php',
    '/order'         => 'public/order.php',
    '/order-details' => 'public/order-details.php',
    '/public'        => 'public/index.php',
    '/admin'         => 'admin/index.php',
    '/admin/'        => 'admin/index.php',
    '/admin/login'   => 'admin/login.php',
    '/admin/logout'  => 'admin/logout.php',
    '/admin/dashboard' => 'admin/dashboard.php',
];

if (isset($friendly[$path])) {
    $target = $friendly[$path];
} else {
    // Direct .php entrypoints under the whitelisted folders only.
    $target = null;
    if (preg_match('#^/public/([a-z0-9_-]+\.php)$#', $path, $m) && is_file($root . '/public/' . $m[1])) {
        $target = 'public/' . $m[1];
    } elseif (preg_match('#^/admin/([a-z0-9_-]+\.php)$#', $path, $m) && is_file($root . '/admin/' . $m[1])) {
        $target = 'admin/' . $m[1];
    } elseif (preg_match('#^/admin/([a-z0-9_-]+)/([a-z0-9_-]+\.php)$#', $path, $m) && is_file($root . '/admin/' . $m[1] . '/' . $m[2])) {
        $target = 'admin/' . $m[1] . '/' . $m[2];
    } elseif (preg_match('#^/api/([a-z0-9_-]+)/([a-z0-9_-]+\.php)$#', $path, $m) && is_file($root . '/api/' . $m[1] . '/' . $m[2])) {
        $target = 'api/' . $m[1] . '/' . $m[2];
    }
}

if ($target === null || !is_file($root . '/' . $target)) {
    http_response_code(404);
    require $root . '/includes/error-404.php';
    exit;
}

// Keep the real script path visible to the app for logging/misc checks.
$_SERVER['SCRIPT_NAME'] = $path;
$_SERVER['SCRIPT_FILENAME'] = $root . '/' . $target;

require $root . '/' . $target;
