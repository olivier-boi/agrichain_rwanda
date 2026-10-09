<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$stats = [
  ['Total Farmers',    db_count("SELECT COUNT(*) FROM farmers"), '👨🏾‍🌾'],
  ['Total Buyers',     db_count("SELECT COUNT(*) FROM buyers"), '🏪'],
  ['Total Products',   db_count("SELECT COUNT(*) FROM products"), '🌽'],
  ['Total Orders',     db_count("SELECT COUNT(*) FROM orders"), '🧾'],
  ['Pending Orders',   db_count("SELECT COUNT(*) FROM orders WHERE status='Pending'"), '⏳'],
  ['Completed Orders', db_count("SELECT COUNT(*) FROM orders WHERE status='Delivered'"), '✅'],
  ['Quality Inspections', db_count("SELECT COUNT(*) FROM quality_inspections"), '🏅'],
];
$recent = db_all("SELECT o.id, o.order_date, o.total_amount, o.status, b.company_name FROM orders o JOIN buyers b ON o.buyer_id=b.id ORDER BY o.id DESC LIMIT 6");
$grades = db_all("SELECT quality_grade, COUNT(*) AS n FROM products GROUP BY quality_grade");
$totalP = max(1, array_sum(array_column($grades, 'n')));
$waiting = db_count("SELECT COUNT(*) FROM products WHERE quality_grade='Pending'");
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-cards seven">
<?php foreach ($stats as $s): ?><div class="dash-card"><span><?= $s[2] ?></span><div><div class="num" data-count="<?= $s[1] ?>">0</div><p><?= $s[0] ?></p></div></div><?php endforeach; ?>
</div>
<div class="dash-two">
  <div class="panel"><div class="panel-head"><h3>Recent Orders</h3><a href="orders.php">Manage orders</a></div>
    <div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Buyer</th><th>Total</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($recent as $o): ?><tr><td>#<?= $o['id'] ?></td><td><?= e($o['company_name']) ?></td><td><?= money($o['total_amount']) ?></td><td><?= status_badge($o['status']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div></div>
  <div class="panel"><div class="panel-head"><h3>Product Quality Overview</h3></div>
    <?php foreach ($grades as $g): $pct = round($g['n'] / $totalP * 100); ?>
      <div class="bar-row"><span><?= grade_badge($g['quality_grade']) ?></span><div class="bar"><div class="bar-fill grade-fill-<?= strtolower($g['quality_grade']) ?>" style="width:<?= $pct ?>%"></div></div><b><?= $g['n'] ?></b></div>
    <?php endforeach; ?>
    <?php if ($waiting): ?><p class="alert alert-info mt"><?= $waiting ?> product(s) waiting for inspection.</p><?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
