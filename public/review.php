<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Customer Reviews - Food Factory';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (!rate_limit('review_submit', 5, 300)) {
        flash('error', 'Too many submissions. Please try again later.');
        redirect('/public/review.php');
    }

    $v = new Validator($_POST);
    $v->required('name', 'Name')->maxLength('name', 120, 'Name')
      ->required('rating', 'Rating')->range('rating', 1, 5, 'Rating')
      ->required('review', 'Review')->maxLength('review', 1000, 'Review');

    if ($v->fails()) {
        $errors = $v->errors();
        set_old($_POST);
    } else {
        $user = current_user();
        $stmt = db()->prepare(
            'INSERT INTO reviews (user_id, name, rating, review_text, status)
             VALUES (:uid, :name, :rating, :text, "pending")'
        );
        $stmt->execute([
            'uid'    => $user['id'] ?? null,
            'name'   => trim($_POST['name']),
            'rating' => (int)$_POST['rating'],
            'text'   => trim($_POST['review']),
        ]);
        clear_old();
        flash('success', 'Thanks for your review! It will appear once approved by our team.');
        redirect('/public/review.php');
    }
}

$reviews = db()->query(
    "SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC LIMIT 20"
)->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<section class="reviews-page section-wrap">
    <div style="text-align:center;margin-bottom:48px;">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:8px;">Testimonials</div>
        <h2>Customer Reviews</h2>
        <p class="review-text" style="max-width:600px;margin:0 auto;">Discover what our valued guests have to say about their dining experience at Food Factory.</p>
    </div>

    <div class="reviews-container">
        <?php if (!$reviews): ?>
            <p style="color:var(--text-muted);text-align:center;width:100%;">No reviews yet — be the first to share your experience!</p>
        <?php endif; ?>
        <?php foreach ($reviews as $r): ?>
            <div class="review-card">
                <div class="review-avatar"><?= strtoupper(substr($r['name'], 0, 1)) ?></div>
                <h3><?= e($r['name']) ?></h3>
                <div class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
                <p>"<?= e($r['review_text']) ?>"</p>
                <?php if (!empty($r['admin_reply'])): ?>
                    <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted);text-align:left;">
                        <strong style="color:var(--gold);">Food Factory:</strong> <?= e($r['admin_reply']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="write-review section-wrap">
    <div style="max-width:540px;margin:0 auto;background:var(--card-bg);border:1px solid var(--border);padding:40px;border-radius:20px;box-shadow:var(--shadow-lg);">
        <h2>Write a Review</h2>
        <p class="review-form-text">Share your thoughts on our food and service.</p>

        <?php if ($errors): ?>
            <div class="alert alert-error" style="margin-bottom:20px;text-align:left;">
                <span>⚠️</span>
                <div><?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?></div>
            </div>
        <?php endif; ?>

        <form action="/public/review.php" method="POST">
            <?= csrf_field() ?>
            <input type="text" name="name" placeholder="Your Name" value="<?= old('name') ?>" required>

            <!-- Star rating UI -->
            <div style="text-align:left;margin-bottom:16px;">
                <label style="font-size:13px;color:var(--text-muted);display:block;margin-bottom:6px;">Your Rating:</label>
                <div class="star-rating" id="star-rating">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <input type="hidden" name="rating" id="rating-value" value="<?= old('rating') ?: '' ?>" required>
            </div>

            <textarea name="review" rows="4" placeholder="Write your review..." required><?= old('review') ?></textarea>
            <button type="submit" class="btn" style="width:100%;justify-content:center;">⭐ Submit Review</button>
        </form>
    </div>
</section>

<?php clear_old(); require __DIR__ . '/../includes/footer.php'; ?>
