<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Renders a hidden CSRF input. Echo this inside every <form>. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Call at the top of every POST handler. Aborts the request on mismatch. */
function csrf_verify(): void
{
    $submitted = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($submitted) || !hash_equals($_SESSION['_csrf'] ?? '', $submitted)) {
        http_response_code(419);
        require __DIR__ . '/error-419.php';
        exit;
    }
}
