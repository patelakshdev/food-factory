<?php
// order.php was previously empty. It now enters the real ordering flow:
// browse the dynamic menu, add items to cart, then checkout.
require_once __DIR__ . '/../config/app.php';
redirect('/public/menu.php');
