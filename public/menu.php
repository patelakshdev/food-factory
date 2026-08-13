<?php
require_once __DIR__ . '/../config/app.php';

$pageTitle = 'Menu - Food Factory';

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = trim((string)($_GET['q'] ?? ''));

$categories = db()->query("SELECT * FROM categories WHERE status = 'active' ORDER BY display_order")->fetchAll();

$sql = "SELECT mi.*, c.name AS category_name FROM menu_items mi
        JOIN categories c ON c.id = mi.category_id
        WHERE mi.status IN ('active','out_of_stock')";
$params = [];
if ($categoryId > 0) {
    $sql .= ' AND mi.category_id = :category_id';
    $params['category_id'] = $categoryId;
}
if ($search !== '') {
    $sql .= ' AND mi.name LIKE :search';
    $params['search'] = '%' . $search . '%';
}
$sql .= ' ORDER BY c.display_order, mi.display_order, mi.name';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">

<section class="section-wrap">
    <h2>Our Menu</h2>

    <form method="get" class="menu-filters">
        <input type="text" name="q" placeholder="Search dishes..." value="<?= e($search) ?>">
        <button type="submit" class="<?= $categoryId === 0 ? 'active' : '' ?>">All</button>
        <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= (int)$cat['id'] ?><?= $search !== '' ? '&q=' . urlencode($search) : '' ?>">
                <button type="button" class="<?= $categoryId === (int)$cat['id'] ? 'active' : '' ?>"
                    onclick="window.location.href='?category=<?= (int)$cat['id'] ?><?= $search !== '' ? '&q=' . urlencode($search) : '' ?>'">
                    <?= e($cat['name']) ?>
                </button>
            </a>
        <?php endforeach; ?>
    </form>

    <?php if (!$items): ?>
        <p>No dishes match your search. Try a different keyword or category.</p>
    <?php else: ?>
        <div class="item-grid">
            <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <img src="/assets/images/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>">
                    <div class="item-body">
                        <h4><?= e($item['name']) ?></h4>
                        <p><?= e($item['description']) ?></p>
                        <div class="price"><?= money((float)$item['price']) ?></div>
                        <?php if ($item['status'] === 'out_of_stock'): ?>
                            <button class="add-btn" disabled>Out of Stock</button>
                        <?php else: ?>
                            <button class="add-btn" data-add-to-cart data-item-id="<?= (int)$item['id'] ?>">Add to Cart</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
