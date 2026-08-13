<?php
/**
 * Generates the Vercel static preview.
 *
 * Vercel cannot execute PHP, so this script pre-renders every navigable
 * public page to static HTML (PHP CLI fetches the page from the local dev
 * server — which has live MySQL — and writes the final HTML), then Vercel
 * serves those files with rewrites so the original /public/*.php links work.
 *
 * Usage: php deploy/preview-build.php
 * Output: preview/
 */

declare(strict_types=1);

$base = 'http://127.0.0.1:8080';
$root = __DIR__ . '/..';
$out  = $root . '/preview';

// Pages that make sense static + render without a logged-in session.
$pages = [
    'index'             => '/public/index.php',
    'about'             => '/public/about.php',
    'menu'              => '/public/menu.php',
    'gallery'           => '/public/gallery.php',
    'review'            => '/public/review.php',
    'reservation'       => '/public/reservation.php',
    'contact'           => '/public/contact.php',
    'login'             => '/public/login.php',
    'register'          => '/public/register.php',
    'cart'              => '/public/cart.php',
];

function fetch(string $url): string
{
    $ctx = stream_context_create(['http' => [
        'timeout'     => 30,
        'ignore_errors' => true,
        'header'      => "User-Agent: ff-preview-builder\r\n",
    ]]);
    $html = @file_get_contents($url, false, $ctx);
    if ($html === false) {
        throw new RuntimeException("Failed to fetch {$url}");
    }
    return $html;
}

@mkdir($out . '/public', 0777, true);
@mkdir($out . '/assets', 0777, true);

foreach ($pages as $slug => $url) {
    try {
        $html = fetch($base . $url);
        file_put_contents($out . "/public/{$slug}.html", $html);
        echo "[ok] $slug ({$url})\n";
    } catch (Throwable $e) {
        echo "[skip] $slug : {$e->getMessage()}\n";
    }
}

// Copy static assets (css/js/images) so /assets/* resolves.
$candidates = [
    'css'    => glob($root . '/assets/css/*.*'),
    'js'     => glob($root . '/assets/js/*.*'),
    'images' => glob($root . '/assets/images/*.*'),
];
foreach ($candidates as $dir => $files) {
    if (!is_dir("$out/assets/$dir")) {
        mkdir("$out/assets/$dir", 0777, true);
    }
    foreach ($files as $f) {
        copy($f, "$out/assets/$dir/" . basename($f));
    }
}
echo "[ok] assets copied\n";

// A tiny 404 page (Vercel fallback).
file_put_contents(
    "$out/404.html",
    '<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;padding-top:100px;">'
    . '<h1>404</h1><p>This page is not available in the static preview.</p>'
    . '<p><a href="/">Go to Home</a></p></body></html>'
);

echo "done -> {$out}\n";