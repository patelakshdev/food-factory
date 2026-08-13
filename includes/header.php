<?php
/** @var string $pageTitle set by the including page before require */
$cartCount = function_exists('get_cart_count') ? get_cart_count() : 0;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($pageTitle ?? 'Food Factory') ?></title>
<link rel="stylesheet" href="/food-factory/assets/css/style4.css">
<link rel="stylesheet" href="/food-factory/assets/css/site.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<header>
    <nav>
        <div class="logo">
            <img src="/food-factory/assets/images/img.jpg" alt="Restaurant Logo">
            <h2>FOOD FACTORY</h2>
        </div>
        <div class="menu-toggle" id="menu-toggle">☰</div>
        <ul id="nav-menu">
            <li><a href="/public/index.php">Home</a></li>
            <li><a href="/public/about.php">About</a></li>
            <li><a href="/public/menu.php">Menu</a></li>
            <li><a href="/public/review.php">Reviews</a></li>
            <li><a href="/public/gallery.php">Gallery</a></li>
            <li><a href="/public/reservation.php">Reservation</a></li>
            <li><a href="/public/contact.php">Contact</a></li>
            <li><a href="/public/order.php">Order</a></li>
            <li><a href="/public/cart.php" class="cart-link">🛒 Cart<?php if ($cartCount > 0): ?><span class="cart-badge"><?= (int)$cartCount ?></span><?php endif; ?></a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="/public/orders.php">My Orders</a></li>
                <li><a href="/public/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="/public/login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<?php $flashError = flash('error'); $flashSuccess = flash('success'); ?>
<?php if ($flashError): ?><div class="alert alert-error"><?= e($flashError) ?></div><?php endif; ?>
<?php if ($flashSuccess): ?><div class="alert alert-success"><?= e($flashSuccess) ?></div><?php endif; ?>
