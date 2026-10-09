<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');
$f = current_farmer();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['delete_id'] ?? 0);
    $p = db_one("SELECT id FROM products WHERE id=? AND farmer_id=?", 'ii', [$pid, $f['id']]);   // ownership check
    if (!$p) { flash('Product not found.', 'error'); }
    elseif (db_count("SELECT COUNT(*) FROM order_items WHERE product_id=?", 'i', [$pid]) > 0) { flash('This product has orders and cannot be deleted. Set its quantity to 0 instead.', 'error'); }
    else { db_exec("DELETE FROM products WHERE id=?", 'i', [$pid]); flash('Product deleted.'); }
    redirect('farmer/products.php');
}
$products = db_all("SELECT p.*, c.name AS category FROM products p JOIN categories c ON p.category_id=c.id WHERE p.farmer_id=? ORDER BY p.id DESC", 'i', [$f['id']]);
$page_title = 'My Products';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="panel"><div class="panel-head"><h3><?= count($products) ?> products</h3><a href="add-product.php" class="btn btn-sm">+ Add Product</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>Image</th><th>Product</th><th>Category</th><th>Qty (kg)</th><th>Price/kg</th><th>Grade</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($products as $p): ?>
<tr><td class="thumb"><?= product_visual($p) ?></td><td><?= e($p['name']) ?></td><td><?= e($p['category']) ?></td><td><?= number_format($p['quantity_kg']) ?></td><td><?= number_format($p['price_per_kg']) ?></td>
<td><?= grade_badge($p['quality_grade']) ?></td><td><?= e($p['availability']) ?></td>
<td class="actions"><a class="btn btn-sm btn-outline" href="edit-product.php?id=<?= $p['id'] ?>">Edit</a>
<form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= $p['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Delete this product?">Delete</button></form></td></tr>
<?php endforeach; ?>
<?php if (!$products): ?><tr><td colspan="8" class="empty-msg">You have not added any products yet.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
