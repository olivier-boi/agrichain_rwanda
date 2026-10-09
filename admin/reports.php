<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$byStatus = db_all("SELECT status, COUNT(*) AS n, COALESCE(SUM(total_amount),0) AS amount FROM orders GROUP BY status");
$byProduct = db_all("SELECT p.name, SUM(oi.quantity_kg) AS kg, SUM(oi.subtotal) AS amount FROM order_items oi JOIN products p ON oi.product_id=p.id JOIN orders o ON oi.order_id=o.id WHERE o.status<>'Cancelled' GROUP BY p.id ORDER BY amount DESC LIMIT 8");
$byGrade = db_all("SELECT quality_grade, COUNT(*) AS n FROM products GROUP BY quality_grade ORDER BY quality_grade");
$topFarmers = db_all("SELECT u.full_name, COUNT(DISTINCT o.id) AS orders, SUM(oi.subtotal) AS amount FROM order_items oi JOIN orders o ON oi.order_id=o.id JOIN products p ON oi.product_id=p.id JOIN farmers f ON p.farmer_id=f.id JOIN users u ON f.user_id=u.id WHERE o.status<>'Cancelled' GROUP BY f.id ORDER BY amount DESC LIMIT 5");
$revenue = db_one("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE status<>'Cancelled'")['t'];
$page_title = 'Reports';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="panel"><div class="panel-head"><h3>Total order value (excluding cancelled): <?= money($revenue) ?></h3><button class="btn btn-sm" onclick="window.print()">🖨 Print report</button></div></div>
<div class="dash-two">
<div class="panel"><h3>Orders by status</h3><table class="table"><thead><tr><th>Status</th><th>Orders</th><th>Value</th></tr></thead><tbody>
<?php foreach ($byStatus as $r): ?><tr><td><?= status_badge($r['status']) ?></td><td><?= $r['n'] ?></td><td><?= money($r['amount']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="panel"><h3>Products by quality grade</h3><table class="table"><thead><tr><th>Grade</th><th>Products</th></tr></thead><tbody>
<?php foreach ($byGrade as $r): ?><tr><td><?= grade_badge($r['quality_grade']) ?></td><td><?= $r['n'] ?></td></tr><?php endforeach; ?></tbody></table></div>
</div>
<div class="dash-two">
<div class="panel"><h3>Best-selling products</h3><table class="table"><thead><tr><th>Product</th><th>Kg sold</th><th>Value</th></tr></thead><tbody>
<?php foreach ($byProduct as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= number_format($r['kg']) ?></td><td><?= money($r['amount']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="panel"><h3>Top farmers by sales</h3><table class="table"><thead><tr><th>Farmer</th><th>Orders</th><th>Value</th></tr></thead><tbody>
<?php foreach ($topFarmers as $r): ?><tr><td><?= e($r['full_name']) ?></td><td><?= $r['orders'] ?></td><td><?= money($r['amount']) ?></td></tr><?php endforeach; ?></tbody></table></div>
</div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
