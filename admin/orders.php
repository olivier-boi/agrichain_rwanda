<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$statuses = ['Pending','Confirmed','Processing','In Transit','Delivered','Cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? ''; $oid = (int)($_POST['id'] ?? 0);
    if ($act === 'status') {
        if (update_order_status($oid, $_POST['status'] ?? '')) flash("Order #$oid updated."); else flash('Status could not be changed (delivered or cancelled orders are final).', 'error');
    } elseif ($act === 'delete') {
        $o = db_one("SELECT status FROM orders WHERE id=?", 'i', [$oid]);
        if ($o && $o['status'] === 'Cancelled') { db_exec("DELETE FROM orders WHERE id=?", 'i', [$oid]); flash('Order deleted.'); }
        else flash('Only cancelled orders can be deleted.', 'error');
    }
    redirect('admin/orders.php');
}
$filter = in_array($_GET['status'] ?? '', $statuses) ? $_GET['status'] : '';
$sql = "SELECT o.*, b.company_name, GROUP_CONCAT(p.name SEPARATOR ', ') AS products, GROUP_CONCAT(DISTINCT fu.full_name SEPARATOR ', ') AS farmers, SUM(oi.quantity_kg) AS qty
        FROM orders o JOIN buyers b ON o.buyer_id=b.id JOIN order_items oi ON oi.order_id=o.id JOIN products p ON oi.product_id=p.id
        JOIN farmers f ON p.farmer_id=f.id JOIN users fu ON f.user_id=fu.id " . ($filter ? "WHERE o.status=? " : "") . "GROUP BY o.id ORDER BY o.id DESC";
$orders = $filter ? db_all($sql, 's', [$filter]) : db_all($sql);
$view = isset($_GET['view']) ? db_one("SELECT o.*, b.company_name FROM orders o JOIN buyers b ON o.buyer_id=b.id WHERE o.id=?", 'i', [(int)$_GET['view']]) : null;
$page_title = 'Manage Orders';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<?php if ($view): $items = db_all("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?", 'i', [$view['id']]); ?>
<div class="panel"><div class="panel-head"><h3>Order #<?= $view['id'] ?> - <?= e($view['company_name']) ?></h3><a href="orders.php">Close</a></div>
<p>Date: <?= e($view['order_date']) ?> &middot; Delivery: <?= e($view['delivery_location']) ?> &middot; <?= status_badge($view['status']) ?></p>
<div class="table-wrap"><table class="table"><thead><tr><th>Product</th><th>Quantity (kg)</th><th>Unit price</th><th>Subtotal</th></tr></thead><tbody>
<?php foreach ($items as $it): ?><tr><td><?= e($it['name']) ?></td><td><?= number_format($it['quantity_kg']) ?></td><td><?= money($it['unit_price']) ?></td><td><?= money($it['subtotal']) ?></td></tr><?php endforeach; ?>
<tr><td colspan="3"><b>Total</b></td><td><b><?= money($view['total_amount']) ?></b></td></tr></tbody></table></div></div>
<?php endif; ?>
<div class="tabs"><a href="orders.php" class="<?= !$filter ? 'active' : '' ?>">All</a><?php foreach ($statuses as $s): ?><a href="?status=<?= urlencode($s) ?>" class="<?= $filter === $s ? 'active' : '' ?>"><?= $s ?></a><?php endforeach; ?></div>
<div class="panel"><div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Buyer</th><th>Product</th><th>Farmer</th><th>Qty</th><th>Total</th><th>Date</th><th>Delivery</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($orders as $o): ?><tr><td>#<?= $o['id'] ?></td><td><?= e($o['company_name']) ?></td><td><?= e($o['products']) ?></td><td><?= e($o['farmers']) ?></td><td><?= number_format($o['qty']) ?></td><td><?= money($o['total_amount']) ?></td><td><?= e(substr($o['order_date'], 0, 10)) ?></td><td><?= e($o['delivery_location']) ?></td><td><?= status_badge($o['status']) ?></td>
<td class="actions"><a class="btn btn-sm btn-outline" href="?view=<?= $o['id'] ?>">View</a>
<?php if (!in_array($o['status'], ['Delivered', 'Cancelled'])): ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $o['id'] ?>"><select name="status"><?php foreach ($statuses as $s): ?><option <?= $s === $o['status'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select><button class="btn btn-sm" data-confirm="Change status?">Save</button></form><?php endif; ?>
<?php if ($o['status'] === 'Cancelled'): ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $o['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Delete this order?">Delete</button></form><?php endif; ?></td></tr><?php endforeach; ?>
<?php if (!$orders): ?><tr><td colspan="10" class="empty-msg">No orders found.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
