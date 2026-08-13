<?php
/** @var string $pageTitle set by the including page before require */
$cartCount = function_exists('get_cart_count') ? get_cart_count() : 0;
$currentUser = function_exists('current_user') ? current_user() : null;
$firstName = $currentUser ? e($currentUser['first_name'] ?? 'Account') : '';
$avatarLetter = $firstName ? strtoupper($firstName[0]) : 'U';
?><?php $csrfToken = function_exists('csrf_token') ? csrf_token() : ''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Food Factory – Fresh ingredients, great taste, and memorable dining. Order online, book a table, and explore our menu.">
    <title><?= e($pageTitle ?? 'Food Factory') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style4.css">
    <link rel="stylesheet" href="/assets/css/site.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍔</text></svg>">
</head>
<body>

<!-- Nav overlay for mobile drawer -->
<div class="nav-overlay" id="nav-overlay"></div>

<header>
    <nav>
        <!-- Logo -->
        <a href="/public/index.php" class="logo" style="text-decoration:none;">
            <div class="logo-icon">🍔</div>
            <h2>FOOD <span>FACTORY</span></h2>
        </a>

        <!-- Hamburger toggle -->
        <div class="menu-toggle" id="menu-toggle" aria-label="Toggle menu" role="button" tabindex="0">
            <span></span><span></span><span></span>
        </div>

        <!-- Nav links -->
        <ul id="nav-menu">
            <li><a href="/public/index.php">Home</a></li>
            <li><a href="/public/about.php">About</a></li>
            <li><a href="/public/menu.php">Menu</a></li>
            <li><a href="/public/review.php">Reviews</a></li>
            <li><a href="/public/gallery.php">Gallery</a></li>
            <li><a href="/public/reservation.php">Reservation</a></li>
            <li><a href="/public/contact.php">Contact</a></li>
            <li>
                <a href="/public/cart.php" class="cart-link" id="nav-cart-link">
                    🛒 Cart<?php if ($cartCount > 0): ?><span class="cart-badge" id="nav-cart-badge"><?= (int)$cartCount ?></span><?php endif; ?>
                </a>
            </li>
            <?php if (is_logged_in()): ?>
                <li>
                    <div class="nav-dropdown" style="display:inline-block;">
                        <button class="nav-user-btn" id="user-menu-btn">
                            <div class="nav-user-avatar"><?= $avatarLetter ?></div>
                            <?= $firstName ?>
                            <span>▾</span>
                        </button>
                        <div class="nav-dropdown-menu" id="user-dropdown">
                            <a href="/public/orders.php">📦 My Orders</a>
                            <a href="/public/logout.php" style="color:#f87171;">🚪 Logout</a>
                        </div>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="/public/login.php">Sign In</a></li>
                <li><a href="/public/register.php" class="btn" style="padding:8px 18px;font-size:13px;border-radius:50px;">Create Account</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<?php $flashError = flash('error'); $flashSuccess = flash('success'); ?>
<?php if ($flashError): ?><div class="alert alert-error" style="margin:10px auto;max-width:960px;padding:0 24px;"><span>⚠️</span><?= e($flashError) ?></div><?php endif; ?>
<?php if ($flashSuccess): ?><div class="alert alert-success" style="margin:10px auto;max-width:960px;padding:0 24px;"><span>✅</span><?= e($flashSuccess) ?></div><?php endif; ?>

<script>
// User dropdown toggle
(function(){
  var btn = document.getElementById('user-menu-btn');
  var menu = document.getElementById('user-dropdown');
  if(btn && menu){
    btn.addEventListener('click', function(e){
      e.stopPropagation();
      menu.classList.toggle('open');
    });
    document.addEventListener('click', function(){ menu.classList.remove('open'); });
  }
})();
</script>
