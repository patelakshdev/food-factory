<?php
/**
 * Idempotent first-boot provisioning for the app container.
 * Runs before Apache starts, on every container start:
 *   1. Waits for the database to accept connections.
 *   2. Applies database/schema.sql then database/seed.sql (both idempotent:
 *      CREATE TABLE IF NOT EXISTS / INSERT ... ON DUPLICATE KEY UPDATE).
 *   3. Ensures the admin account exists with the password from env
 *      (ADMIN_EMAIL / ADMIN_PASSWORD), safe to re-run.
 */

declare(strict_types=1);

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_DATABASE') ?: 'food_factory';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

$dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

$pdo = null;
for ($i = 1; $i <= 30; $i++) {
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "[boot] database ready\n";
        break;
    } catch (PDOException $e) {
        echo "[boot] waiting for database ({$i}/30): " . $e->getMessage() . "\n";
        sleep(2);
    }
}

if ($pdo === null) {
    fwrite(STDERR, "[boot] FATAL: could not connect to database\n");
    exit(1);
}

$root = __DIR__ . '/..';

function schema_installed(PDO $pdo, string $dbName): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = :db AND table_name IN ('users','menu_items','orders','roles')"
    );
    $stmt->execute(['db' => $dbName]);
    return (int)$stmt->fetchColumn() >= 4;
}

// schema.sql contains multiple statements; split on ; lines (discard line comments)
function run_sql_file(PDO $pdo, string $path): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Cannot read SQL file: {$path}");
    }
    // Strip `-- ...` and `/* ... */` comment lines, then split on `;`
    $stripped = preg_replace('#^\s*--.*$#m', '', $sql);
    $stripped = preg_replace('#/\*.*?\*/#s', '', $stripped);
    foreach (explode(';', $stripped) as $statement) {
        $statement = trim($statement);
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
}

// Provision schema + seed only ONCE: when this database has no schema yet.
// seed.sql is not fully idempotent (plain INSERTs into role_permissions,
// categories, settings, coupons), so it must never run against an already
// seeded database. Docker volume / managed DB state survives restarts, which
// is exactly why we guard on emptiness here.
if (!schema_installed($pdo, $name)) {
    run_sql_file($pdo, $root . '/database/schema.sql');
    echo "[boot] schema applied\n";
    run_sql_file($pdo, $root . '/database/seed.sql');
    echo "[boot] seed applied\n";
} else {
    echo "[boot] schema already present, skipping seed\n";
}

// Ensure the admin account uses a real (env-provided) password, never the
// placeholder hash shipped in seed.sql.
$adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@foodfactory.local';
$adminPassword = getenv('ADMIN_PASSWORD');
if ($adminPassword !== false && $adminPassword !== '') {
    $hash = password_hash($adminPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = :r LIMIT 1');
    $stmt->execute(['r' => 'super_admin']);
    $roleId = (int)($stmt->fetchColumn() ?: 5);

    $stmt = $pdo->prepare(
        'INSERT INTO users (first_name, last_name, email, phone, password_hash, role_id, status, email_verified_at)
         VALUES (:fn, :ln, :email, :phone, :hash, :role, "active", NOW())
         ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), status = "active", role_id = VALUES(role_id)'
    );
    $stmt->execute([
        'fn'    => 'Food Factory',
        'ln'    => 'Admin',
        'email' => $adminEmail,
        'phone' => '+918141214421',
        'hash'  => $hash,
        'role'  => $roleId,
    ]);
    echo "[boot] admin account ensured ({$adminEmail})\n";
} else {
    echo "[boot] WARN: ADMIN_PASSWORD not set; admin password left as seeded (placeholder).\n";
}

echo "[boot] done\n";