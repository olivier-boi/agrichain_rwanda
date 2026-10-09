<?php
// includes/dash_header.php - top + sidebar for farmer / buyer / admin dashboards.
// Before including, the page must call require_role() and set $page_title.
require_once __DIR__ . '/auth.php';
$role = user()['role'];
$menus = [
  'admin' => [['Dashboard','admin/dashboard.php'],['Farmers','admin/farmers.php'],['Buyers','admin/buyers.php'],['Products','admin/products.php'],
              ['Categories','admin/categories.php'],['Orders','admin/orders.php'],['Quality Inspections','admin/quality.php'],
              ['Supply Chain','admin/supply-chain.php'],['Users','admin/users.php'],['Reports','admin/reports.php'],['Settings','admin/settings.php']],
  'farmer' => [['Dashboard','farmer/dashboard.php'],['My Products','farmer/products.php'],['Add Product','farmer/add-product.php'],['Orders','farmer/orders.php'],
               ['Quality Reports','farmer/quality.php'],['Supply Chain Status','farmer/supply-chain.php'],['Profile','farmer/profile.php']],
  'buyer'  => [['Dashboard','buyer/dashboard.php'],['Browse Marketplace','marketplace.php'],['My Orders','buyer/orders.php'],['Order History','buyer/orders.php?view=history'],['Profile','buyer/profile.php']],
];
$here = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title ?? 'Dashboard') ?> | AgriChain Rwanda</title>
<link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body class="dash-body">
<aside class="sidebar" id="sidebar">
  <a href="<?= url('index.php') ?>" class="side-logo">🌱 Agri<b>Chain</b> Rwanda</a>
  <div class="side-role"><?= ucfirst($role) ?> Panel</div>
  <nav>
    <?php foreach ($menus[$role] as $m):
        $file = basename(parse_url($m[1], PHP_URL_PATH));
        $folder = dirname($m[1]);
        $isActive = ($file === $here && strpos($_SERVER['SCRIPT_NAME'], $folder) !== false && strpos($m[1], '?') === false) ? 'active' : ''; ?>
      <a href="<?= url($m[1]) ?>" class="<?= $isActive ?>"><?= $m[0] ?></a>
    <?php endforeach; ?>
    <a href="<?= url('logout.php') ?>" class="logout-link" data-confirm="Do you want to log out?">Logout</a>
  </nav>
</aside>
<div class="dash-main">
  <div class="topbar">
    <button class="side-toggle" id="sideToggle" aria-label="Menu">☰</button>
    <h1><?= e($page_title ?? 'Dashboard') ?></h1>
    <div class="topbar-right">
      <a href="<?= url('notifications.php') ?>" class="bell" title="Notifications">🔔<?php if (($n = unread_count()) > 0): ?><span class="bell-count"><?= $n ?></span><?php endif; ?></a>
      <span class="user-chip"><?= e(user()['name']) ?></span>
    </div>
  </div>
  <main class="dash-content">
