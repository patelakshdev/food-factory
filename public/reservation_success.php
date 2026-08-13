<?php
require_once __DIR__ . '/../config/app.php';
$name = trim((string)($_GET['name'] ?? 'Guest'));
$pageTitle = 'Reservation Confirmed - Food Factory';
require __DIR__ . '/../includes/header.php';
?>

<div class="section-wrap">
    <div class="box">
        <div class="success-icon">✓</div>
        <h1>Table Reserved!</h1>
        <p>Thank you, <strong><?= e($name) ?></strong>. Your reservation request has been received.</p>
        <p style="font-size:13px;color:var(--text-muted);">Our team will review your booking and send a confirmation shortly.</p>
        <div style="margin-top:28px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="/public/menu.php" class="btn">View Menu</a>
            <a href="/public/index.php" class="btn-outline">Back to Home</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
