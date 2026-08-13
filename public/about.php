<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'About Us - Food Factory';
require __DIR__ . '/../includes/header.php';
?>
<section class="about" id="About">
    <div class="about-image">
        <img src="/assets/images/about.jpg" alt="Restaurant Interior">
    </div>
    <div class="about-content">
        <h2>About Food Factory</h2>
        <p>
            Welcome to <strong>Food Factory</strong>, where great food and exceptional service come together. Since our beginning,
            we have been committed to serving freshly prepared dishes made with high-quality ingredients and authentic recipes.
            Every meal is crafted with care to provide a delicious and memorable dining experience.
        </p>
        <p>
            Our menu offers a wide variety of pizzas, burgers, pasta, sandwiches, beverages, and desserts to satisfy every taste.
            Whether you are visiting for a quick lunch, a family dinner, or a celebration with friends,
            Food Factory is the perfect place to enjoy fresh food in a warm and welcoming atmosphere.
        </p>
        <p>
            Customer satisfaction is our highest priority. Our dedicated team focuses on maintaining excellent food quality,
            hygiene, and fast service. We believe every guest deserves a comfortable dining experience and leaves with a smile.
        </p>
        <h3>Our Mission</h3>
        <p>
            Our mission is to provide delicious, affordable, and high-quality food while maintaining outstanding customer service and a clean,
            friendly environment for everyone.
        </p>
        <h3>Our Vision</h3>
        <p>
            Our vision is to become one of the most trusted and loved restaurants by continuously improving our menu,
            embracing innovation, and creating memorable experiences for every customer.
        </p>
        <a href="/public/menu.php" class="about-btn">Explore Menu</a>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
