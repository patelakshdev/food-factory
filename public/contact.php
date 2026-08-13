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

<section class="contact section-wrap" id="Contact" style="max-width:1100px;">
    <div style="text-align:center;margin-bottom:48px;">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:8px;">We'd love to hear from you</div>
        <h2 style="font-family:'Outfit',sans-serif;font-size:clamp(28px,4vw,42px);font-weight:800;color:var(--white);">Contact Us</h2>
    </div>

    <div class="contact-container">
        <div class="contact-info" style="background:var(--card-bg);border:1px solid var(--border);padding:36px;border-radius:20px;">
            <h3>Get In Touch</h3>

            <div class="contact-info-item">
                <div class="contact-info-icon">📍</div>
                <div class="contact-info-text">
                    <strong>Address</strong>
                    <span>123 Food Bazar, Nikol, Ahmedabad, Gujarat</span>
                </div>
            </div>

            <div class="contact-info-item">
                <div class="contact-info-icon">📞</div>
                <div class="contact-info-text">
                    <strong>Phone</strong>
                    <span>+91 81412 14421</span>
                </div>
            </div>

            <div class="contact-info-item">
                <div class="contact-info-icon">📧</div>
                <div class="contact-info-text">
                    <strong>Email</strong>
                    <span>foodfactory@gmail.com</span>
                </div>
            </div>

            <div class="contact-info-item">
                <div class="contact-info-icon">🕒</div>
                <div class="contact-info-text">
                    <strong>Opening Hours</strong>
                    <span>Monday – Sunday<br>10:00 AM – 11:00 PM</span>
                </div>
            </div>
        </div>

        <div class="contact-form" style="background:var(--card-bg);border:1px solid var(--border);padding:36px;border-radius:20px;">
            <h3>Send a Message</h3>
            <?php if ($errors): ?>
                <div class="alert alert-error" style="margin-bottom:20px;">
                    <span>⚠️</span>
                    <div><?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?></div>
                </div>
            <?php endif; ?>
            <form action="/public/contact.php" method="POST">
                <?= csrf_field() ?>
                <input type="text" name="name" placeholder="Your Name" value="<?= old('name') ?>" required>
                <input type="email" name="email" placeholder="Your Email" value="<?= old('email') ?>" required>
                <textarea name="message" rows="5" placeholder="Your Message" required><?= old('message') ?></textarea>
                <button type="submit" class="btn" style="width:100%;justify-content:center;">✉️ Send Message</button>
            </form>
        </div>
    </div>
</section>

<?php clear_old(); require __DIR__ . '/../includes/footer.php'; ?>
