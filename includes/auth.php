<?php
declare(strict_types=1);

/** Returns the logged-in user's DB row (cached per-request), or null. */
function current_user(): ?array
{
    static $user = null;
    static $loaded = false;

    if ($loaded) {
        return $user;
    }

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT u.*, r.name AS role_name
         FROM users u JOIN roles r ON r.id = u.role_id
         WHERE u.id = :id AND u.status = "active" LIMIT 1'
    );
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $row = $stmt->fetch();
    if ($row) {
        $user = $row;
        $loaded = true;
    }

    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin_role(): bool
{
    $u = current_user();
    return $u !== null && in_array($u['role_name'], ['staff', 'manager', 'admin', 'super_admin'], true);
}

/** Checks whether the current user's role has a given permission key. */
function user_can(string $permissionKey): bool
{
    $u = current_user();
    if ($u === null) {
        return false;
    }
    static $cache = [];
    $roleId = (int)$u['role_id'];
    if (!isset($cache[$roleId])) {
        $stmt = db()->prepare(
            'SELECT p.permission_key FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id'
        );
        $stmt->execute(['role_id' => $roleId]);
        $cache[$roleId] = array_column($stmt->fetchAll(), 'permission_key');
    }
    return in_array($permissionKey, $cache[$roleId], true);
}

/** Registers a new customer. Returns [true, userId] or [false, errorMessage]. */
function register_user(string $firstName, string $lastName, string $email, string $phone, string $password): array
{
    $existing = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $existing->execute(['email' => $email]);
    if ($existing->fetch()) {
        return [false, 'An account with that email already exists.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = db()->prepare(
        'INSERT INTO users (first_name, last_name, email, phone, password_hash, role_id, status)
         VALUES (:first_name, :last_name, :email, :phone, :hash, 1, "active")'
    );
    $stmt->execute([
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'email'      => $email,
        'phone'      => $phone,
        'hash'       => $hash,
    ]);

    return [true, (int)db()->lastInsertId()];
}

/** Attempts login. Returns [true, null] or [false, errorMessage]. Includes lockout after repeated failures. */
function attempt_login(string $email, string $password): array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        return [false, 'Invalid email or password.'];
    }

    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        return [false, 'Too many failed attempts. Please try again later.'];
    }

    if ($user['status'] !== 'active') {
        return [false, 'This account is not active.'];
    }

    if (!password_verify($password, $user['password_hash'])) {
        $attempts = (int)$user['failed_login_attempts'] + 1;
        $lockUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null; // 15 min lockout
        $upd = db()->prepare('UPDATE users SET failed_login_attempts = :a, locked_until = :l WHERE id = :id');
        $upd->execute(['a' => $attempts, 'l' => $lockUntil, 'id' => $user['id']]);
        return [false, 'Invalid email or password.'];
    }

    // Success: reset counters, rotate session id, log the user in.
    $upd = db()->prepare(
        'UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = :id'
    );
    $upd->execute(['id' => $user['id']]);

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];

    return [true, null];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** Guards a customer-only page. */
function require_login(string $redirectTo = '/public/login.php'): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect($redirectTo);
    }
}

/** Guards an admin page behind a specific permission. */
function require_permission(string $permissionKey): void
{
    if (!is_logged_in() || !is_admin_role()) {
        redirect('/admin/login.php');
    }
    if (!user_can($permissionKey)) {
        http_response_code(403);
        require __DIR__ . '/error-403.php';
        exit;
    }
}
