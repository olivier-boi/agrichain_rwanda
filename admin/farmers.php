<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$errors = []; $showForm = isset($_GET['add']);
$edit = isset($_GET['edit']) ? db_one("SELECT f.*, u.full_name, u.email, u.phone FROM farmers f JOIN users u ON f.user_id=u.id WHERE f.id=?", 'i', [(int)$_GET['edit']]) : null;
if ($edit) $showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'add') {
        $errors = validate_account($_POST);
        if (trim($_POST['farm_location'] ?? '') === '' || trim($_POST['main_products'] ?? '') === '' || (float)($_POST['farm_size_ha'] ?? 0) <= 0) $errors[] = 'Farm location, main products and farm size are required.';
        if (!$errors) { $r = create_farmer($_POST); if ($r === true) { flash('Farmer added.'); redirect('admin/farmers.php'); } $errors[] = $r; }
        $showForm = true;
    } elseif ($act === 'edit') {
        $fid = (int)$_POST['id'];
        $row = db_one("SELECT f.user_id, u.email FROM farmers f JOIN users u ON f.user_id=u.id WHERE f.id=?", 'i', [$fid]);
        $errors = validate_account(array_merge($_POST, ['email' => $row['email'] ?? 'x@x.rw']), false);
        if (!$row) $errors[] = 'Farmer not found.';
        if (!$errors) {
            db_exec("UPDATE users SET full_name=?, phone=? WHERE id=?", 'ssi', [trim($_POST['full_name']), trim($_POST['phone']), $row['user_id']]);
            db_exec("UPDATE farmers SET location_id=?, farm_location=?, main_products=?, farm_size_ha=? WHERE id=?", 'issdi',
                    [(int)$_POST['location_id'], trim($_POST['farm_location']), trim($_POST['main_products']), (float)$_POST['farm_size_ha'], $fid]);
            flash('Farmer updated.'); redirect('admin/farmers.php');
        }
        $edit = db_one("SELECT f.*, u.full_name, u.email, u.phone FROM farmers f JOIN users u ON f.user_id=u.id WHERE f.id=?", 'i', [$fid]); $showForm = true;
    } elseif ($act === 'delete') {
        $fid = (int)$_POST['id'];
        $row = db_one("SELECT user_id FROM farmers WHERE id=?", 'i', [$fid]);
        $orders = db_count("SELECT COUNT(*) FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE p.farmer_id=?", 'i', [$fid]);
        if (!$row) flash('Farmer not found.', 'error');
        elseif ($orders > 0) flash('This farmer has products with orders and cannot be deleted. Deactivate the account in Users instead.', 'error');
        else { db_exec("DELETE FROM users WHERE id=?", 'i', [$row['user_id']]); flash('Farmer deleted.'); }
        redirect('admin/farmers.php');
    }
}
$view = isset($_GET['view']) ? db_one("SELECT f.*, u.full_name, u.email, u.phone, u.status, l.district, l.sector FROM farmers f JOIN users u ON f.user_id=u.id JOIN locations l ON f.location_id=l.id WHERE f.id=?", 'i', [(int)$_GET['view']]) : null;
$farmers = db_all("SELECT f.*, u.full_name, u.email, u.phone, l.district, (SELECT COUNT(*) FROM products p WHERE p.farmer_id=f.id) AS total_products
                   FROM farmers f JOIN users u ON f.user_id=u.id JOIN locations l ON f.location_id=l.id ORDER BY f.id DESC");
$page_title = 'Manage Farmers';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<?php if ($view): $vp = db_all("SELECT name, quantity_kg, price_per_kg, quality_grade FROM products WHERE farmer_id=?", 'i', [$view['id']]); ?>
<div class="panel"><div class="panel-head"><h3>Farmer Details</h3><a href="farmers.php">Close</a></div>
<table class="kv-table"><tr><th>Name</th><td><?= e($view['full_name']) ?></td></tr><tr><th>Email / Phone</th><td><?= e($view['email']) ?> / <?= e($view['phone']) ?></td></tr>
<tr><th>Location</th><td><?= e($view['district'] . ' - ' . $view['sector']) ?>, <?= e($view['farm_location']) ?></td></tr><tr><th>Farm size</th><td><?= e($view['farm_size_ha']) ?> ha</td></tr>
<tr><th>Main products</th><td><?= e($view['main_products']) ?></td></tr><tr><th>Account</th><td><?= e($view['status']) ?></td></tr></table>
<h4 class="mt">Products</h4><ul><?php foreach ($vp as $p): ?><li><?= e($p['name']) ?> - <?= number_format($p['quantity_kg']) ?> kg @ <?= number_format($p['price_per_kg']) ?> RWF <?= grade_badge($p['quality_grade']) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<?php if ($showForm): $v = $edit ?: $_POST; ?>
<div class="panel"><div class="panel-head"><h3><?= $edit ? 'Edit Farmer' : 'Add Farmer' ?></h3><a href="farmers.php">Cancel</a></div>
<?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" class="form-card wide flat" data-validate><?= csrf_field() ?>
<input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>"><?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
<div class="form-grid">
<label>Full name <input type="text" name="full_name" required value="<?= e($v['full_name'] ?? '') ?>"></label>
<label>Email <input type="email" name="email" required <?= $edit ? 'readonly' : '' ?> value="<?= e($v['email'] ?? '') ?>"></label>
<label>Phone <input type="tel" name="phone" required pattern="07[2389][0-9]{7}" value="<?= e($v['phone'] ?? '') ?>"></label>
<label>District / Sector <select name="location_id" required><?= location_options($v['location_id'] ?? 0) ?></select></label>
<label>Farm location <input type="text" name="farm_location" required value="<?= e($v['farm_location'] ?? '') ?>"></label>
<label>Farm size (ha) <input type="number" step="0.01" min="0.1" name="farm_size_ha" required value="<?= e($v['farm_size_ha'] ?? '') ?>"></label>
<label class="<?= $edit ? 'full' : '' ?>">Main products <input type="text" name="main_products" required value="<?= e($v['main_products'] ?? '') ?>"></label>
<?php if (!$edit): ?><label>Password <input type="password" name="password" required minlength="6"></label><?php endif; ?>
</div><button class="btn btn-lg">Save Farmer</button></form></div>
<?php endif; ?>

<div class="panel"><div class="panel-head"><h3><?= count($farmers) ?> farmers</h3><a href="?add=1" class="btn btn-sm">+ Add Farmer</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>District</th><th>Main products</th><th>Products</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($farmers as $f): ?><tr><td><?= $f['id'] ?></td><td><?= e($f['full_name']) ?><br><small><?= e($f['email']) ?></small></td><td><?= e($f['phone']) ?></td><td><?= e($f['district']) ?></td><td><?= e($f['main_products']) ?></td><td><?= $f['total_products'] ?></td>
<td class="actions"><a class="btn btn-sm btn-outline" href="?view=<?= $f['id'] ?>">View</a><a class="btn btn-sm" href="?edit=<?= $f['id'] ?>">Edit</a>
<form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $f['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Delete this farmer?">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
