<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');
$b = current_buyer(); $bid = $b['id'];
$total   = db_count("SELECT COUNT(*) FROM orders WHERE buyer_id=?", 'i', [$bid]);
$pending = db_count("SELECT COUNT(*) FROM orders WHERE buyer_id=? AND status IN ('Pending','Confirmed','Processing','In Transit')", 'i', [$bid]);
$done    = db_count("SELECT COUNT(*) FROM orders WHERE buyer_id=? AND status='Delivered'", 'i', [$bid]);
$spent   = db_one("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE buyer_id=? AND status<>'Cancelled'", 'i', [$bid])['t'];
$recent  = db_all("SELECT * FROM orders WHERE buyer_id=? ORDER BY id DESC LIMIT 5", 'i', [$bid]);
$page_title = 'Buyer Dashboard';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="welcome-card"><h2>Welcome, <?= e($b['company_name']) ?> 👋</h2><p><?= e($b['business_type']) ?> &middot; <?= e($b['full_name']) ?></p>
  <a href="<?= url('marketplace.php') ?>" class="btn btn-gold">Browse Marketplace</a></div>
<div class="dash-cards">
  <div class="dash-card"><span>🧾</span><div><div class="num" data-count="<?= $total ?>">0</div><p>Total Orders</p></div></div>
  <div class="dash-card"><span>⏳</span><div><div class="num" data-count="<?= $pending ?>">0</div><p>Active Orders</p></div></div>
  <div class="dash-card"><span>✅</span><div><div class="num" data-count="<?= $done ?>">0</div><p>Delivered</p></div></div>
  <div class="dash-card"><span>💰</span><div><div class="num small"><?= money($spent) ?></div><p>Total Spent</p></div></div>
</div>
<div class="panel"><div class="panel-head"><h3>Recent Orders</h3><a href="orders.php">View all</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th></tr></thead><tbody>
<?php foreach ($recent as $o): ?><tr><td>#<?= $o['id'] ?></td><td><?= e(substr($o['order_date'], 0, 10)) ?></td><td><?= money($o['total_amount']) ?></td><td><?= status_badge($o['status']) ?></td></tr><?php endforeach; ?>
<?php if (!$recent): ?><tr><td colspan="4" class="empty-msg">No orders yet. Visit the marketplace to start.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
