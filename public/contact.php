<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Contact Us - Food Factory';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (!rate_limit('contact_submit', 8, 300)) {
        flash('error', 'Too many attempts. Please try again later.');
        redirect('/public/contact.php');
    }

    $v = new Validator($_POST);
    $v->required('name', 'Name')->maxLength('name', 120, 'Name')
      ->required('email', 'Email')->email('email')
      ->required('message', 'Message')->maxLength('message', 2000, 'Message');

    if ($v->fails()) {
        $errors = $v->errors();
        set_old($_POST);
    } else {
        $stmt = db()->prepare(
            'INSERT INTO contact_messages (name, email, message, status) VALUES (:name, :email, :message, "new")'
        );
        $stmt->execute([
            'name'    => trim($_POST['name']),
            'email'   => trim($_POST['email']),
            'message' => trim($_POST['message']),
        ]);
        clear_old();
        redirect('/public/contact_success.php');
    }
}

require __DIR__ . '/../includes/header.php';
?>

<section class="contact section-wrap" id="Contact">
    <h2>Contact Us</h2>
    <div class="contact-container">
        <div class="contact-info">
            <h3>Get In Touch</h3>
            <p><strong>📍 Address:</strong><br>123 Food Bazar, Nikol, Ahmedabad, Gujarat</p>
            <p><strong>📞 Phone:</strong><br>+91 81412 14421</p>
            <p><strong>📧 Email:</strong><br>foodfactory@gmail.com</p>
            <p><strong>🕒 Opening Hours:</strong><br>Monday - Sunday<br>10:00 AM - 11:00 PM</p>
        </div>
        <div class="contact-form">
            <h3>Send a Message</h3>
            <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?></div><?php endif; ?>
            <form action="/public/contact.php" method="POST">
                <?= csrf_field() ?>
                <input type="text" name="name" placeholder="Your Name" value="<?= old('name') ?>" required>
                <input type="email" name="email" placeholder="Your Email" value="<?= old('email') ?>" required>
                <textarea name="message" rows="6" placeholder="Your Message" required><?= old('message') ?></textarea>
                <button type="submit" class="btn">Send Message</button>
            </form>
        </div>
    </div>
</section>

<?php clear_old(); require __DIR__ . '/../includes/footer.php'; ?>
