<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');
$b = current_buyer();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oid = (int)($_POST['cancel_id'] ?? 0);
    $o = db_one("SELECT id, status FROM orders WHERE id=? AND buyer_id=?", 'ii', [$oid, $b['id']]);   // my order only
    if ($o && $o['status'] === 'Pending' && update_order_status($oid, 'Cancelled')) flash("Order #$oid cancelled.");
    else flash('Only pending orders can be cancelled.', 'error');
    redirect('buyer/orders.php');
}
$history = ($_GET['view'] ?? '') === 'history';
$where = $history ? "o.status IN ('Delivered','Cancelled')" : "o.status NOT IN ('Delivered','Cancelled')";
$orders = db_all("SELECT o.*, GROUP_CONCAT(CONCAT(p.name,' (',FORMAT(oi.quantity_kg,0),' kg)') SEPARATOR ', ') AS items,
                         GROUP_CONCAT(DISTINCT fu.full_name SEPARATOR ', ') AS farmers, MAX(sc.current_stage) AS stage
                  FROM orders o JOIN order_items oi ON oi.order_id=o.id JOIN products p ON oi.product_id=p.id
                  JOIN farmers f ON p.farmer_id=f.id JOIN users fu ON f.user_id=fu.id LEFT JOIN supply_chain sc ON sc.order_id=o.id
                  WHERE o.buyer_id=? AND $where GROUP BY o.id ORDER BY o.id DESC", 'i', [$b['id']]);
$page_title = $history ? 'Order History' : 'My Orders';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="tabs"><a href="orders.php" class="<?= !$history ? 'active' : '' ?>">Active Orders</a><a href="orders.php?view=history" class="<?= $history ? 'active' : '' ?>">Order History</a></div>
<div class="panel"><div class="table-wrap"><table class="table"><thead><tr><th>Order ID</th><th>Product(s)</th><th>Farmer</th><th>Total</th><th>Date</th><th>Delivery</th><th>Status</th><th>Tracking</th><th></th></tr></thead><tbody>
<?php foreach ($orders as $o): ?>
<tr><td>#<?= $o['id'] ?></td><td><?= e($o['items']) ?></td><td><?= e($o['farmers']) ?></td><td><?= money($o['total_amount']) ?></td><td><?= e(substr($o['order_date'], 0, 10)) ?></td><td><?= e($o['delivery_location']) ?></td><td><?= status_badge($o['status']) ?></td>
<td><?php if ($o['stage'] && $o['status'] !== 'Cancelled'): ?><div class="mini-progress" title="Stage <?= $o['stage'] ?> of 7"><div style="width:<?= round($o['stage'] / 7 * 100) ?>%"></div></div><small><?= e(chain_stages()[$o['stage'] - 1]) ?></small><?php else: ?>-<?php endif; ?></td>
<td><?php if ($o['status'] === 'Pending'): ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="cancel_id" value="<?= $o['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Cancel this order?">Cancel</button></form><?php endif; ?></td></tr>
<?php endforeach; ?>
<?php if (!$orders): ?><tr><td colspan="9" class="empty-msg">No orders here.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
