<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Create Account - Food Factory';

if (is_logged_in()) {
    redirect('/public/orders.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (!rate_limit('register', 10, 300)) {
        flash('error', 'Too many attempts. Please try again later.');
        redirect('/public/register.php');
    }

    $v = new Validator($_POST);
    $v->required('first_name', 'First name')->maxLength('first_name', 80, 'First name')
      ->required('email', 'Email')->email('email')
      ->required('phone', 'Phone')->phone('phone')
      ->required('password', 'Password')->minLength('password', 8, 'Password');

    if (($_POST['password'] ?? '') !== ($_POST['password_confirm'] ?? '')) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }

    if ($v->fails() || $errors) {
        $errors = array_merge($v->errors(), $errors);
        set_old($_POST);
    } else {
        [$ok, $result] = register_user(
            trim($_POST['first_name']),
            trim($_POST['last_name'] ?? ''),
            strtolower(trim($_POST['email'])),
            trim($_POST['phone']),
            $_POST['password']
        );
        if (!$ok) {
            $errors['email'] = $result;
            set_old($_POST);
        } else {
            [$loginOk] = attempt_login(strtolower(trim($_POST['email'])), $_POST['password']);
            clear_old();
            flash('success', 'Welcome to Food Factory!');
            redirect('/public/orders.php');
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card" style="max-width:500px;">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon">🍔</div>
            <h1>FOOD <span>FACTORY</span></h1>
            <p>Create your account — it's free!</p>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <div><?php foreach ($errors as $e2) echo '<div>' . e($e2) . '</div>'; ?></div>
            </div>
        <?php endif; ?>

        <form method="post" id="register-form" novalidate>
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-field">
                    <input type="text" name="first_name" id="first-name" placeholder="First Name"
                           value="<?= old('first_name') ?>" required autocomplete="given-name">
                    <label for="first-name">First Name</label>
                </div>
                <div class="form-field">
                    <input type="text" name="last_name" id="last-name" placeholder="Last Name"
                           value="<?= old('last_name') ?>" autocomplete="family-name">
                    <label for="last-name">Last Name</label>
                </div>
            </div>

            <div class="form-field">
                <input type="email" name="email" id="reg-email" placeholder="Email address"
                       value="<?= old('email') ?>" required autocomplete="email">
                <label for="reg-email">Email address</label>
            </div>

            <div class="form-field">
                <input type="tel" name="phone" id="reg-phone" placeholder="Phone number"
                       value="<?= old('phone') ?>" required autocomplete="tel">
                <label for="reg-phone">Phone number</label>
            </div>

            <div class="form-field" style="position:relative;">
                <input type="password" name="password" id="password-input" placeholder="Password (min 8 characters)"
                       required autocomplete="new-password">
                <label for="password-input">Password</label>
                <span class="pwd-toggle" onclick="togglePwd('password-input', this)" title="Show/hide">👁</span>
            </div>
            <!-- Password strength meter -->
            <div class="pwd-strength">
                <div class="pwd-strength-bar"><div class="pwd-strength-fill" id="pwd-strength-fill"></div></div>
                <span class="pwd-strength-text" id="pwd-strength-text"></span>
            </div>

            <div class="form-field" style="position:relative;">
                <input type="password" name="password_confirm" id="password-confirm-input"
                       placeholder="Confirm password" required autocomplete="new-password">
                <label for="password-confirm-input">Confirm Password</label>
                <span class="field-icon" id="confirm-icon"></span>
            </div>

            <button type="submit" class="btn" style="width:100%;justify-content:center;margin-top:8px;border-radius:8px;">
                Create Account →
            </button>
        </form>

        <div class="auth-divider"><span>Already have an account?</span></div>

        <a href="/public/login.php" class="btn2" style="width:100%;justify-content:center;border-radius:8px;text-align:center;">
            Sign In
        </a>

        <div class="auth-trust">🔒 Your data is safe and never shared</div>
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

<?php clear_old(); require __DIR__ . '/../includes/footer.php'; ?>
