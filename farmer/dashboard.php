<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');
$f = current_farmer();
$fid = $f['id'];
$totalProducts = db_count("SELECT COUNT(*) FROM products WHERE farmer_id=?", 'i', [$fid]);
$pendingQ      = db_count("SELECT COUNT(*) FROM products WHERE farmer_id=? AND quality_grade='Pending'", 'i', [$fid]);
$totalOrders   = db_count("SELECT COUNT(DISTINCT o.id) FROM orders o JOIN order_items oi ON oi.order_id=o.id JOIN products p ON oi.product_id=p.id WHERE p.farmer_id=?", 'i', [$fid]);
$revenue       = db_one("SELECT COALESCE(SUM(oi.subtotal),0) AS t FROM order_items oi JOIN orders o ON oi.order_id=o.id JOIN products p ON oi.product_id=p.id WHERE p.farmer_id=? AND o.status='Delivered'", 'i', [$fid])['t'];
$latest = db_all("SELECT p.name, p.quantity_kg, p.price_per_kg, p.quality_grade, p.availability FROM products p WHERE p.farmer_id=? ORDER BY p.id DESC LIMIT 5", 'i', [$fid]);
$notes  = db_all("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 5", 'i', [user()['id']]);
$page_title = 'Farmer Dashboard';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="welcome-card"><h2>Muraho, <?= e($f['full_name']) ?> 👋</h2><p><?= e($f['farm_location']) ?> &middot; <?= e($f['farm_size_ha']) ?> ha &middot; <?= e($f['main_products']) ?></p></div>
<div class="dash-cards">
  <div class="dash-card"><span>🌽</span><div><div class="num" data-count="<?= $totalProducts ?>">0</div><p>My Products</p></div></div>
  <div class="dash-card"><span>⏳</span><div><div class="num" data-count="<?= $pendingQ ?>">0</div><p>Awaiting Grading</p></div></div>
  <div class="dash-card"><span>🧾</span><div><div class="num" data-count="<?= $totalOrders ?>">0</div><p>Orders Received</p></div></div>
  <div class="dash-card"><span>💰</span><div><div class="num small"><?= money($revenue) ?></div><p>Delivered Revenue</p></div></div>
</div>
<div class="dash-two">
  <div class="panel"><div class="panel-head"><h3>Latest Products</h3><a href="add-product.php" class="btn btn-sm">+ Add Product</a></div>
    <div class="table-wrap"><table class="table"><thead><tr><th>Product</th><th>Qty (kg)</th><th>Price</th><th>Grade</th></tr></thead><tbody>
    <?php foreach ($latest as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= number_format($p['quantity_kg']) ?></td><td><?= number_format($p['price_per_kg']) ?></td><td><?= grade_badge($p['quality_grade']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div></div>
  <div class="panel"><div class="panel-head"><h3>Notifications</h3><a href="../notifications.php">View all</a></div>
    <?php foreach ($notes as $n): ?><div class="notif <?= $n['is_read'] ? '' : 'unread' ?>"><?= e($n['message']) ?></div><?php endforeach; ?>
    <?php if (!$notes): ?><p class="muted">No notifications.</p><?php endif; ?></div>
</div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
