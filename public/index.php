<?php
require_once __DIR__ . '/../config/app.php';

$pageTitle = 'Food Factory - Home';

$featured = db()->query(
    "SELECT * FROM menu_items WHERE status = 'active' ORDER BY is_featured DESC, id ASC LIMIT 3"
)->fetchAll();

$reviews = db()->query(
    "SELECT name, rating, review_text FROM reviews WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3"
)->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<section class="hero" id="Home">
    <h2>Welcome to Food Factory</h2>
    <p>Fresh Ingredients • Great Taste • Memorable Dining</p>
    <div class="hero-buttons">
        <a href="/public/menu.php" class="btn">View Menu</a>
        <a href="/public/reservation.php" class="btn2">Book a Table</a>
    </div>
</section>

<section class="about" id="About">
    <div class="about-image">
        <img src="/assets/images/about.jpg" alt="Restaurant Interior">
    </div>
    <div class="about-content">
        <h2>About Food Factory</h2>
        <p>
            Welcome to <strong>Food Factory</strong>, where delicious food meets
            quality service. We prepare every dish using fresh ingredients and
            authentic recipes to give our customers the best dining experience.
        </p>
        <p>
            Whether you're visiting for lunch, dinner, or a celebration with
            friends and family, our team is committed to making every meal
            memorable.
        </p>
        <a href="/public/about.php" class="about-btn">Read More</a>
    </div>
</section>

<section class="menu" id="Menu">
    <h2>Our Menu</h2>
    <div class="menu-container">
        <?php foreach ($featured as $item): ?>
            <div class="menu-card">
                <img src="/assets/images/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>">
                <h3><?= e($item['name']) ?></h3>
                <p><?= e($item['description']) ?></p>
                <h4><?= money((float)$item['price']) ?></h4>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="menu-btn">
        <a href="/public/menu.php" class="btn">View Full Menu</a>
    </div>
</section>

<section class="reviews" id="Reviews">
    <h2>Customer Reviews</h2>
    <p class="review-text">
        Customer satisfaction is our greatest achievement. Read genuine feedback from our valued guests.
    </p>
    <div class="reviews-container">
        <?php if ($reviews): ?>
            <?php foreach ($reviews as $r): ?>
                <div class="review-card">
                    <h3><?= e($r['name']) ?></h3>
                    <div class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
                    <p>"<?= e($r['review_text']) ?>"</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews yet — be the first to share your experience!</p>
        <?php endif; ?>
    </div>
    <div class="reviews-btn">
        <a href="/public/review.php" class="btn3">View All Reviews</a>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
