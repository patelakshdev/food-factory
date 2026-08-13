<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Admin Login - Food Factory';

if (is_logged_in() && is_admin_role()) {
    redirect('/admin/dashboard.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (!rate_limit('admin_login', 8, 300)) {
        $error = 'Too many attempts. Please try again later.';
    } else {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        [$ok, $err] = attempt_login($email, $password);
        if ($ok && is_admin_role()) {
            audit_log(current_user()['id'], 'admin.login');
            redirect('/admin/dashboard.php');
        }
        if ($ok && !is_admin_role()) {
            logout_user();
            $error = 'This account does not have admin access.';
        } else {
            $error = $err;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/css/site.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="background:#1f2430;">
<div class="login-box">
    <h2>Food Factory Admin</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <input type="email" name="email" placeholder="Admin Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn" style="width:100%;">Login</button>
    </form>
</div>
</body>
</html>
