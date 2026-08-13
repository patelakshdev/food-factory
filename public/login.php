<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Login - Food Factory';

if (is_logged_in()) {
    redirect('/public/orders.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (!rate_limit('login', 10, 300)) {
        $error = 'Too many attempts. Please try again later.';
    } else {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        [$ok, $err] = attempt_login($email, $password);
        if ($ok) {
            redirect('/public/orders.php');
        }
        $error = $err;
    }
}

require __DIR__ . '/../includes/header.php';
?>
<section class="section-wrap">
    <div class="login-box">
        <h2>Login</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn" style="width:100%;">Login</button>
        </form>
        <p style="margin-top:14px;font-size:14px;">New here? <a href="/public/register.php">Create an account</a></p>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
