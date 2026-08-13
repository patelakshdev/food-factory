<?php
declare(strict_types=1);

/** Escape output to prevent XSS. Use this around EVERY variable printed into HTML. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function money(float $amount): string
{
    return '₹' . number_format($amount, 2);
}

function generate_order_number(): string
{
    return 'FF' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

function old(string $field, string $default = ''): string
{
    return e($_SESSION['_old'][$field] ?? $default);
}

function set_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

/** Write an admin-action audit log entry. */
function audit_log(?int $adminId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details, ip_address)
             VALUES (:admin_id, :action, :entity_type, :entity_id, :details, :ip)'
        );
        $stmt->execute([
            'admin_id'    => $adminId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'details'     => $details,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        error_log('[AUDIT LOG FAILED] ' . $e->getMessage());
    }
}

/** Very small rate limiter backed by the session (per action key). */
function rate_limit(string $key, int $maxAttempts, int $windowSeconds): bool
{
    $now = time();
    $bucket = $_SESSION['_rate'][$key] ?? ['count' => 0, 'reset' => $now + $windowSeconds];

    if ($now > $bucket['reset']) {
        $bucket = ['count' => 0, 'reset' => $now + $windowSeconds];
    }

    $bucket['count']++;
    $_SESSION['_rate'][$key] = $bucket;

    return $bucket['count'] <= $maxAttempts;
}
