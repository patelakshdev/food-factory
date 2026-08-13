<?php
require_once __DIR__ . '/../config/app.php';

$pageTitle = 'Food Factory - Fresh Food, Great Taste';

$featured = db()->query(
    "SELECT * FROM menu_items WHERE status = 'active' ORDER BY is_featured DESC, id ASC LIMIT 3"
)->fetchAll();

$reviews = db()->query(
    "SELECT name, rating, review_text FROM reviews WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3"
)->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">

<!-- ── HERO ── -->
<section class="hero" id="Home">
    <div class="hero-bg" id="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-tag">🔥 Now accepting online orders</div>
        <h2>Taste the <span class="highlight">Passion</span><br>Behind Every Bite</h2>
        <p>Fresh ingredients, authentic recipes, and a dining experience you'll never forget.</p>
        <div class="hero-buttons">
            <a href="/public/menu.php" class="btn">🍽️ Explore Menu</a>
            <a href="/public/reservation.php" class="btn2">📅 Book a Table</a>
        </div>
    </div>
    <div class="hero-scroll">
        <span class="arrow">↓</span>
        <span>Scroll to explore</span>
    </div>
</section>

<!-- ── STATS BAR ── -->
<div class="stats-bar">
    <div class="stat-item">
        <div class="stat-num" data-target="5000" data-suffix="+">5000+</div>
        <div class="stat-label">Orders Delivered</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" data-target="120" data-suffix="+">120+</div>
        <div class="stat-label">Menu Items</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" data-target="4800" data-suffix="+">4800+</div>
        <div class="stat-label">Happy Customers</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" data-target="5" data-suffix="★">5★</div>
        <div class="stat-label">Avg. Rating</div>
    </div>
</div>

<!-- ── ABOUT ── -->
<section class="about" id="About">
    <div class="about-image">
        <img src="/assets/images/about.jpg" alt="Restaurant Interior" loading="lazy">
    </div>
    <div class="about-content">
        <span class="label">Our Story</span>
        <h2>Where Food Meets Passion</h2>
        <p>
            Welcome to <strong>Food Factory</strong>, where great food and exceptional service come together.
            Every dish is crafted with fresh, high-quality ingredients and authentic recipes to deliver
            a memorable dining experience.
        </p>
        <div class="about-features">
            <div class="about-feat"><div class="icon">🌿</div><span>Fresh ingredients daily</span></div>
            <div class="about-feat"><div class="icon">👨‍🍳</div><span>Expert chefs</span></div>
            <div class="about-feat"><div class="icon">⚡</div><span>Fast delivery</span></div>
            <div class="about-feat"><div class="icon">🏆</div><span>Award-winning taste</span></div>
        </div>
        <a href="/public/about.php" class="about-btn">Learn More →</a>
    </div>
</section>

<!-- ── FEATURED MENU ── -->
<section class="menu" id="Menu">
    <h2>⭐ Featured Dishes</h2>
    <div class="menu-container">
        <?php foreach ($featured as $item): ?>
            <div class="menu-card">
                <img src="/assets/images/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                <h3><?= e($item['name']) ?></h3>
                <p><?= e($item['description']) ?></p>
                <h4><?= money((float)$item['price']) ?></h4>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="menu-btn">
        <a href="/public/menu.php" class="btn">View Full Menu →</a>
    </div>
</section>

<!-- ── WHY CHOOSE US ── -->
<section class="why-us">
    <div class="section-heading">
        <span class="label">Why Us</span>
        <h2>Why Food Factory?</h2>
        <p>We believe great food is more than just ingredients — it's an experience.</p>
    </div>
    <div class="why-grid">
        <div class="why-card">
            <div class="why-icon">🌿</div>
            <h3>100% Fresh</h3>
            <p>We source fresh ingredients every morning to guarantee the highest quality in every dish.</p>
        </div>
        <div class="why-card">
            <div class="why-icon">⚡</div>
            <h3>Fast Delivery</h3>
            <p>Your food delivered hot and fresh to your door in under 40 minutes, guaranteed.</p>
        </div>
        <div class="why-card">
            <div class="why-icon">💰</div>
            <h3>Best Value</h3>
            <p>Premium quality at prices that don't break the bank. Enjoy restaurant-quality food affordably.</p>
        </div>
        <div class="why-card">
            <div class="why-icon">🏆</div>
            <h3>Award-Winning</h3>
            <p>Recognized as one of Ahmedabad's top restaurants with thousands of 5-star reviews.</p>
        </div>
    </div>
</section>

<!-- ── REVIEWS ── -->
<section class="reviews" id="Reviews">
    <h2>What Our Customers Say</h2>
    <p class="review-text">Customer satisfaction is our greatest achievement. Read genuine feedback from our valued guests.</p>
    <div class="reviews-container">
        <?php if ($reviews): ?>
            <?php foreach ($reviews as $r): ?>
                <div class="review-card">
                    <div class="review-avatar"><?= strtoupper(substr($r['name'], 0, 1)) ?></div>
                    <h3><?= e($r['name']) ?></h3>
                    <div class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
                    <p>"<?= e($r['review_text']) ?>"</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:var(--text-muted);">No reviews yet — be the first to share your experience!</p>
        <?php endif; ?>
    </div>
    <div class="reviews-btn">
        <a href="/public/review.php" class="btn3">View All Reviews →</a>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
