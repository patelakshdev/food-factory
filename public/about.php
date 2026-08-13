<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'About Us - Food Factory';
require __DIR__ . '/../includes/header.php';
?>

<!-- Hero Banner -->
<div class="about-hero">
    <h1>About <span>Food Factory</span></h1>
    <p>Crafting delicious meals and unforgettable dining moments since 2026.</p>
</div>

<section class="about" id="About" style="padding-top:60px;">
    <div class="about-image">
        <img src="/assets/images/about.jpg" alt="Restaurant Interior" loading="lazy">
    </div>
    <div class="about-content">
        <span class="label">Our Journey</span>
        <h2>Crafted With Quality & Care</h2>
        <p>
            Welcome to <strong>Food Factory</strong>, where delicious food meets exceptional service. Since our beginning,
            we have been committed to serving freshly prepared dishes made with high-quality ingredients and authentic recipes.
            Every meal is crafted with care to provide a delicious and memorable dining experience.
        </p>
        <p>
            Our menu offers a wide variety of pizzas, burgers, pasta, beverages, and desserts to satisfy every craving.
            Whether you are visiting for a quick lunch, a family dinner, or a celebration with friends,
            Food Factory is the perfect place to enjoy fresh food in a warm, welcoming atmosphere.
        </p>

        <div class="mission-vision">
            <div class="mv-card">
                <h3>🎯 Our Mission</h3>
                <p>
                    To provide delicious, affordable, and high-quality food while maintaining outstanding customer service and a clean, friendly environment for everyone.
                </p>
            </div>
            <div class="mv-card">
                <h3>👁️ Our Vision</h3>
                <p>
                    To become one of the most trusted and loved restaurant brands by continuously improving our menu, embracing culinary innovation, and creating memorable experiences.
                </p>
            </div>
        </div>

        <a href="/public/menu.php" class="about-btn">Explore Menu →</a>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
