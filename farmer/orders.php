<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');
$f = current_farmer();
$statuses = ['Pending','Confirmed','Processing','In Transit','Delivered','Cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oid = (int)($_POST['order_id'] ?? 0); $status = $_POST['status'] ?? '';
    $mine = db_count("SELECT COUNT(*) FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=? AND p.farmer_id=?", 'ii', [$oid, $f['id']]);
    if (!$mine) flash('Order not found.', 'error');
    elseif (update_order_status($oid, $status)) flash("Order #$oid updated to $status.");
    else flash('This order cannot be changed (it is already delivered or cancelled).', 'error');
    redirect('farmer/orders.php');
}
$orders = db_all("SELECT o.id, o.order_date, o.status, o.delivery_location, b.company_name,
                         GROUP_CONCAT(p.name SEPARATOR ', ') AS products, SUM(oi.quantity_kg) AS qty, SUM(oi.subtotal) AS amount
                  FROM orders o JOIN order_items oi ON oi.order_id=o.id JOIN products p ON oi.product_id=p.id JOIN buyers b ON o.buyer_id=b.id
                  WHERE p.farmer_id=? GROUP BY o.id ORDER BY o.id DESC", 'i', [$f['id']]);
$page_title = 'Orders Received';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="panel"><div class="table-wrap"><table class="table"><thead><tr><th>Order</th><th>Buyer</th><th>Product</th><th>Qty (kg)</th><th>Amount</th><th>Delivery</th><th>Date</th><th>Status</th><th>Update</th></tr></thead><tbody>
<?php foreach ($orders as $o): ?>
<tr><td>#<?= $o['id'] ?></td><td><?= e($o['company_name']) ?></td><td><?= e($o['products']) ?></td><td><?= number_format($o['qty']) ?></td><td><?= money($o['amount']) ?></td><td><?= e($o['delivery_location']) ?></td><td><?= e(substr($o['order_date'], 0, 10)) ?></td><td><?= status_badge($o['status']) ?></td>
<td><?php if (!in_array($o['status'], ['Delivered', 'Cancelled'])): ?>
<form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= $o['id'] ?>">
<select name="status"><?php foreach ($statuses as $s): ?><option <?= $s === $o['status'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
<button class="btn btn-sm" data-confirm="Update this order status?">Save</button></form><?php else: ?>-<?php endif; ?></td></tr>
<?php endforeach; ?>
<?php if (!$orders): ?><tr><td colspan="9" class="empty-msg">No orders yet.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
