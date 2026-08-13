<?php
require_once __DIR__ . '/../config/app.php';

$pageTitle = 'Our Menu - Food Factory';

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

<div class="section-wrap" style="max-width:1200px;">
    <!-- Page header -->
    <div style="margin-bottom:32px;">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:10px;">What we serve</div>
        <h2 style="font-family:'Outfit',sans-serif;font-size:clamp(28px,4vw,42px);font-weight:800;color:var(--white);margin-bottom:8px;">Our Full Menu</h2>
        <p style="color:var(--text-muted);font-size:15px;">Explore our wide variety of freshly prepared dishes.</p>
    </div>

    <!-- Filters -->
    <form method="get" class="menu-filters" id="menu-filter-form">
        <input type="text" name="q" id="menu-search" placeholder="🔍  Search dishes…" value="<?= e($search) ?>">
        <a href="/public/menu.php"><button type="button" class="<?= $categoryId === 0 && $search === '' ? 'active' : '' ?>">All</button></a>
        <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= (int)$cat['id'] ?><?= $search !== '' ? '&q=' . urlencode($search) : '' ?>">
                <button type="button" class="<?= $categoryId === (int)$cat['id'] ? 'active' : '' ?>">
                    <?= e($cat['name']) ?>
                </button>
            </a>
        <?php endforeach; ?>
        <button type="submit" style="background:var(--accent);color:#fff;border-color:var(--accent);">Search</button>
    </form>

    <!-- Results count -->
    <?php if ($search !== '' || $categoryId > 0): ?>
        <p style="color:var(--text-muted);font-size:13px;margin-bottom:20px;">
            <?= count($items) ?> result<?= count($items) !== 1 ? 's' : '' ?> found
            <?= $search ? 'for "<strong style="color:var(--white);">' . e($search) . '</strong>"' : '' ?>
        </p>
    <?php endif; ?>

    <!-- Items grid -->
    <?php if (!$items): ?>
        <div style="text-align:center;padding:80px 20px;">
            <div style="font-size:64px;margin-bottom:20px;">🍽️</div>
            <h3 style="font-family:'Outfit',sans-serif;color:var(--white);font-size:22px;margin-bottom:10px;">No dishes found</h3>
            <p style="color:var(--text-muted);margin-bottom:24px;">Try a different keyword or browse all categories.</p>
            <a href="/public/menu.php" class="btn">Browse All</a>
        </div>
    <?php else: ?>
        <div class="item-grid">
            <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <div style="position:relative;overflow:hidden;">
                        <img src="/assets/images/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                        <?php if ($item['status'] === 'out_of_stock'): ?>
                            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;">
                                <span style="background:rgba(239,68,68,0.9);color:#fff;padding:6px 14px;border-radius:50px;font-size:12px;font-weight:700;">OUT OF STOCK</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($item['is_featured']): ?>
                            <div style="position:absolute;top:10px;left:10px;background:var(--accent);color:#fff;padding:3px 10px;border-radius:50px;font-size:10px;font-weight:700;">⭐ FEATURED</div>
                        <?php endif; ?>
                    </div>
                    <div class="item-body">
                        <div style="font-size:11px;color:var(--accent);font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;"><?= e($item['category_name']) ?></div>
                        <h4><?= e($item['name']) ?></h4>
                        <p><?= e($item['description']) ?></p>
                        <div class="price"><?= money((float)$item['price']) ?></div>
                        <?php if ($item['status'] === 'out_of_stock'): ?>
                            <button class="add-btn" disabled>Out of Stock</button>
                        <?php else: ?>
                            <button class="add-btn" data-add-to-cart data-item-id="<?= (int)$item['id'] ?>">
                                🛒 Add to Cart
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
