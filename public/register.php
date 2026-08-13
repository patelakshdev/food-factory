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
<section class="section-wrap">
    <div class="login-box">
        <h2>Create Account</h2>
        <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $e2) echo '<div>' . e($e2) . '</div>'; ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="text" name="first_name" placeholder="First Name" value="<?= old('first_name') ?>" required>
            <input type="text" name="last_name" placeholder="Last Name" value="<?= old('last_name') ?>">
            <input type="email" name="email" placeholder="Email" value="<?= old('email') ?>" required>
            <input type="tel" name="phone" placeholder="Phone" value="<?= old('phone') ?>" required>
            <input type="password" name="password" placeholder="Password (min 8 characters)" required>
            <input type="password" name="password_confirm" placeholder="Confirm Password" required>
            <button type="submit" class="btn" style="width:100%;">Create Account</button>
        </form>
        <p style="margin-top:14px;font-size:14px;">Already have an account? <a href="/public/login.php">Log in</a></p>
    </div>
</section>
<?php clear_old(); require __DIR__ . '/../includes/footer.php'; ?>
