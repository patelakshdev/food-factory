<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Sign In - Food Factory';

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

<div class="auth-page">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon">🍔</div>
            <h1>FOOD <span>FACTORY</span></h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><span>⚠️</span><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" id="login-form" novalidate>
            <?= csrf_field() ?>

            <div class="form-field" id="field-email">
                <input type="email" name="email" id="email-input" placeholder="Email address" required
                       value="<?= old('email') ?>" autocomplete="email">
                <label for="email-input">Email address</label>
            </div>

            <div class="form-field" id="field-password" style="position:relative;">
                <input type="password" name="password" id="pwd-input" placeholder="Password" required autocomplete="current-password">
                <label for="pwd-input">Password</label>
                <span class="pwd-toggle" onclick="togglePwd('pwd-input', this)" title="Show/hide password">👁</span>
            </div>

            <button type="submit" class="btn" style="width:100%;justify-content:center;margin-top:8px;border-radius:8px;">
                Sign In →
            </button>
        </form>

        <div class="auth-divider"><span>New to Food Factory?</span></div>

        <a href="/public/register.php" class="btn2" style="width:100%;justify-content:center;border-radius:8px;text-align:center;">
            Create your account
        </a>

        <div class="auth-trust">🔒 Secure & encrypted login</div>
    </div>
</div>

<script>
function togglePwd(id, btn) {
    var inp = document.getElementById(id);
    if (!inp) return;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.textContent = inp.type === 'password' ? '👁' : '🙈';
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
