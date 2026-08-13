<?php
// Router script for PHP built-in web server

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri === '/' || $uri === '') {
    header('Location: /public/index.php');
    exit;
}

$filePath = __DIR__ . $uri;

// If file exists and is not a directory, serve it directly
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// If directory exists with index.php, include it
if (is_dir($filePath) && file_exists(rtrim($filePath, '/') . '/index.php')) {
    require rtrim($filePath, '/') . '/index.php';
    return;
}

return false;
