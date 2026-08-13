<?php
/** @var string $pageTitle */
$admin = current_user();
$navItems = [
    ['orders/', 'Orders', 'orders.view'],
    ['reservations/', 'Reservations', 'reservations.view'],
    ['reviews/', 'Reviews', 'reviews.moderate'],
    ['messages/', 'Messages', 'dashboard.view'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($pageTitle ?? 'Admin - Food Factory') ?></title>
    <link rel="stylesheet" href="/assets/css/site.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="admin-body">
<aside class="admin-sidebar">
    <h2>Food Factory<br><small style="font-weight:400;font-size:12px;">Admin</small></h2>
    <a href="/admin/dashboard.php">Dashboard</a>
    <?php foreach ($navItems as [$path, $label, $perm]): if (user_can($perm)): ?>
        <a href="/admin/<?= e($path) ?>index.php"><?= e($label) ?></a>
    <?php endif; endforeach; ?>
    <a href="/admin/logout.php" style="margin-top:20px;border-top:1px solid #333;">Logout</a>
</aside>
<main class="admin-main">
    <div class="admin-topbar">
        <h2 style="margin:0;"><?= e($pageTitle ?? '') ?></h2>
        <div>Signed in as <strong><?= e($admin['first_name'] ?? '') ?></strong> (<?= e($admin['role_name'] ?? '') ?>)</div>
    </div>
    <?php $flashError = flash('error'); $flashSuccess = flash('success'); ?>
    <?php if ($flashError): ?><div class="alert alert-error"><?= e($flashError) ?></div><?php endif; ?>
    <?php if ($flashSuccess): ?><div class="alert alert-success"><?= e($flashSuccess) ?></div><?php endif; ?>
