<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Reservation Confirmed - Food Factory';
$name = trim((string)($_GET['name'] ?? 'Guest'));
require __DIR__ . '/../includes/header.php';
?>
<section class="section-wrap" style="text-align:center;">
    <div class="box">
        <h1>🎉 Reservation Successful!</h1>
        <p>Thank you, <strong><?= e($name) ?></strong>.</p>
        <p>Your table has been booked and is pending confirmation from our team. You'll receive a confirmation shortly.</p>
        <a href="/public/index.php" class="btn">Back to Home</a>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
