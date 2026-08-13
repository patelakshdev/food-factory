<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Gallery - Food Factory';
require __DIR__ . '/../includes/header.php';
$images = ['interior.jpg' => 'Interior', 'chief.jpg' => 'Chef', 'pizza.jpg' => 'Pizza', 'burger.jpg' => 'Burger',
    'pasta.jpg' => 'Pasta', 'dessert.jpg' => 'Dessert', 'coffee.jpg' => 'Coffee', 'frenchfries.jpg' => 'French Fries',
    'juices.jpg' => 'Juice'];
?>
<section class="gallery">
    <h2>Our Gallery</h2>
    <p class="gallery-text">Explore some of our delicious dishes and the welcoming atmosphere of Food Factory.</p>
    <div class="gallery-container">
        <?php foreach ($images as $file => $alt): ?>
            <div class="gallery-card">
                <img src="/assets/images/<?= e($file) ?>" alt="<?= e($alt) ?>">
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
