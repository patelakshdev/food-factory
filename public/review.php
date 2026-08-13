<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Reviews - Food Factory';

$errors = [];
$submitted = false;

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
    <h2>Customer Reviews</h2>
    <p class="review-text">Customer satisfaction is our greatest achievement. Discover what our valued guests have to say about their experience at Food Factory.</p>

    <div class="reviews-container">
        <?php if (!$reviews): ?>
            <p>No reviews yet — be the first to share your experience!</p>
        <?php endif; ?>
        <?php foreach ($reviews as $r): ?>
            <div class="review-card">
                <h3><?= e($r['name']) ?></h3>
                <div class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
                <p>"<?= e($r['review_text']) ?>"</p>
                <?php if ($r['admin_reply']): ?>
                    <p style="font-size:13px;color:#666;"><strong>Food Factory:</strong> <?= e($r['admin_reply']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="write-review section-wrap">
    <h2>Write a Review</h2>
    <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?></div><?php endif; ?>
    <form action="/public/review.php" method="POST">
        <?= csrf_field() ?>
        <input type="text" name="name" placeholder="Your Name" value="<?= old('name') ?>" required>
        <select name="rating" required>
            <option value="">Select Rating</option>
            <option value="5">★★★★★</option>
            <option value="4">★★★★☆</option>
            <option value="3">★★★☆☆</option>
            <option value="2">★★☆☆☆</option>
            <option value="1">★☆☆☆☆</option>
        </select>
        <textarea name="review" rows="5" placeholder="Write your review..." required><?= old('review') ?></textarea>
        <button type="submit" class="btn">Submit Review</button>
    </form>
</section>

<?php clear_old(); require __DIR__ . '/../includes/footer.php'; ?>
