<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Message Sent - Food Factory';
require __DIR__ . '/../includes/header.php';
?>

<div class="section-wrap">
    <div class="box">
        <div class="success-icon">✉️</div>
        <h1>Message Sent!</h1>
        <p>Thank you for contacting <strong>Food Factory</strong>.</p>
        <p style="font-size:13px;color:var(--text-muted);">We have received your message and our team will get back to you as soon as possible.</p>
        <div style="margin-top:28px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="/public/menu.php" class="btn">Explore Menu</a>
            <a href="/public/index.php" class="btn-outline">Back to Home</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
