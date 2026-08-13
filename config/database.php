<?php
/**
 * Database connection (PDO).
 * Credentials are read from environment variables (.env loaded in app.php),
 * never hard-coded, and this file lives outside /public.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_DATABASE') ?: 'food_factory';
    $user = getenv('DB_USERNAME') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Never leak DSN/credentials or raw PDO errors to the browser.
        error_log('[DB CONNECTION ERROR] ' . $e->getMessage());
        http_response_code(500);
        require __DIR__ . '/../includes/error-500.php';
        exit;
    }

    // On shared/serverless hosts the schema may not exist yet: provision it
    // idempotently on first contact (disabled with APP_DB_PROVISION=false).
    provision_db_if_needed($pdo);

    return $pdo;
}

/**
 * Idempotent first-boot provisioning, mirroring deploy/bootstrap.php so the
 * app works on hosts where no container entrypoint can run (e.g. Vercel):
 *   1. If the core tables are missing, apply database/schema.sql + seed.sql.
 *   2. Always ensure the ADMIN_EMAIL/ADMIN_PASSWORD account exists.
 * Safe to run on every cold start; schema/seed are guarded and idempotent.
 */
function provision_db_if_needed(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    if (getenv('APP_DB_PROVISION') === 'false') {
        return;
    }

    try {
        $stmt = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name IN ('users','menu_items','orders','roles')"
        );
        $missing = (int)$stmt->fetchColumn() < 4;

        if ($missing) {
            foreach ([__DIR__ . '/../database/schema.sql', __DIR__ . '/../database/seed.sql'] as $file) {
                $sql = file_get_contents($file);
                if ($sql === false) {
                    throw new RuntimeException("Cannot read SQL file: {$file}");
                }
                $sql = preg_replace('#^\s*--.*$#m', '', $sql);
                $sql = preg_replace('#/\*.*?\*/#s', '', $sql);
                foreach (explode(';', $sql) as $statement) {
                    $statement = trim($statement);
                    if ($statement !== '') {
                        $pdo->exec($statement);
                    }
                }
            }
            error_log('[PROVISION] schema + seed applied');
        } else {
            error_log('[PROVISION] schema already present, skipped seed');
        }

        ensure_admin_account($pdo);
    } catch (Throwable $e) {
        // The DB user may lack DDL privileges. Log it clearly; pages that
        // depend on the missing tables will surface a 500 until DB_* / grants
        // or APP_DB_PROVISION are corrected.
        error_log('[PROVISION] FAILED: ' . $e->getMessage());
    }
}

/** Ensures the configured admin account exists with the env-provided password. */
function ensure_admin_account(PDO $pdo): void
{
    $email = getenv('ADMIN_EMAIL') ?: 'admin@foodfactory.local';
    $password = getenv('ADMIN_PASSWORD');
    if ($password === false || $password === '') {
        return;
    }

    $roleId = 5;
    try {
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = :r LIMIT 1');
        $stmt->execute(['r' => 'super_admin']);
        $roleId = (int)($stmt->fetchColumn() ?: 5);
    } catch (Throwable $e) {
        error_log('[PROVISION] could not resolve super_admin role: ' . $e->getMessage());
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (first_name, last_name, email, phone, password_hash, role_id, status, email_verified_at)
         VALUES (:fn, :ln, :email, :phone, :hash, :role, "active", NOW())
         ON DUPLICATE KEY UPDATE
            password_hash = VALUES(password_hash),
            status = "active",
            role_id = VALUES(role_id)'
    );
    $stmt->execute([
        'fn'    => 'Food Factory',
        'ln'    => 'Admin',
        'email' => $email,
        'phone' => '+918141214421',
        'hash'  => $hash,
        'role'  => $roleId,
    ]);
    error_log('[PROVISION] admin account ensured (' . $email . ')');
}
