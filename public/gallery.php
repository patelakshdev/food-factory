<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Gallery - Food Factory';
require __DIR__ . '/../includes/header.php';

$images = [
    'interior.jpg'   => 'Interior Atmosphere',
    'chief.jpg'      => 'Our Master Chef',
    'pizza.jpg'      => 'Artisanal Pizza',
    'burger.jpg'     => 'Gourmet Burger',
    'pasta.jpg'      => 'Creamy Pasta',
    'dessert.jpg'    => 'Delicious Desserts',
    'coffee.jpg'     => 'Freshly Brewed Coffee',
    'frenchfries.jpg'=> 'Crispy Fries',
    'juices.jpg'     => 'Fresh Juices'
];
?>

<section class="gallery section-wrap">
    <div style="text-align:center;margin-bottom:48px;">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:8px;">Visual Showcase</div>
        <h2 style="font-family:'Outfit',sans-serif;font-size:clamp(28px,4vw,42px);font-weight:800;color:var(--white);">Our Gallery</h2>
        <p class="gallery-text">Explore some of our delicious dishes and the welcoming atmosphere of Food Factory.</p>
    </div>

    <div class="gallery-container">
        <?php foreach ($images as $file => $alt): ?>
            <div class="gallery-card">
                <img src="/assets/images/<?= e($file) ?>" alt="<?= e($alt) ?>" loading="lazy">
                <div class="gallery-card-overlay">
                    <span><?= e($alt) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
