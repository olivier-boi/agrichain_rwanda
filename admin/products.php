<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$errors = []; $showForm = isset($_GET['add']);
$edit = isset($_GET['edit']) ? db_one("SELECT * FROM products WHERE id=?", 'i', [(int)$_GET['edit']]) : null;
if ($edit) $showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $farmer = (int)$_POST['farmer_id']; $cat = (int)$_POST['category_id']; $loc = (int)$_POST['location_id'];
        $name = trim($_POST['name'] ?? ''); $desc = trim($_POST['description'] ?? '');
        $qty = (float)($_POST['quantity_kg'] ?? -1); $price = (float)($_POST['price_per_kg'] ?? 0);
        $grade = in_array($_POST['quality_grade'] ?? '', ['A','B','C','Pending']) ? $_POST['quality_grade'] : 'Pending';
        if (strlen($name) < 3) $errors[] = 'Product name must have at least 3 characters.';
        if (!$farmer || !$cat || !$loc) $errors[] = 'Select farmer, category and location.';
        if ($qty < 0) $errors[] = 'Quantity cannot be negative.';
        if ($price <= 0) $errors[] = 'Price must be greater than 0.';
        $img = save_product_image($_FILES['image'] ?? []);
        if ($img === false) $errors[] = 'Image must be JPG, PNG or WEBP under 2 MB.';
        if (!$errors) {
            if ($id) {
                db_exec("UPDATE products SET farmer_id=?, category_id=?, location_id=?, name=?, description=?, quantity_kg=?, price_per_kg=?, quality_grade=?, image=COALESCE(?, image) WHERE id=?",
                        'iiissddssi', [$farmer, $cat, $loc, $name, $desc, $qty, $price, $grade, $img, $id]);
                refresh_availability($id); flash('Product updated.');
            } else {
                $pid = db_exec("INSERT INTO products (farmer_id,category_id,location_id,name,description,quantity_kg,price_per_kg,quality_grade,image) VALUES (?,?,?,?,?,?,?,?,?)",
                               'iiissddss', [$farmer, $cat, $loc, $name, $desc, $qty, $price, $grade, $img]);
                db_exec("INSERT INTO supply_chain (product_id, collection_point, current_stage) VALUES (?, 'To be assigned', 2)", 'i', [$pid]);
                refresh_availability($pid); flash('Product added.');
            }
            redirect('admin/products.php');
        }
        $showForm = true; $edit = $id ? db_one("SELECT * FROM products WHERE id=?", 'i', [$id]) : null;
    } elseif ($act === 'delete') {
        $id = (int)$_POST['id'];
        if (db_count("SELECT COUNT(*) FROM order_items WHERE product_id=?", 'i', [$id]) > 0) flash('This product has orders and cannot be deleted.', 'error');
        else { db_exec("DELETE FROM products WHERE id=?", 'i', [$id]); flash('Product deleted.'); }
        redirect('admin/products.php');
    }
}
$view = isset($_GET['view']) ? db_one("SELECT p.*, c.name AS category, u.full_name AS farmer, l.district FROM products p JOIN categories c ON p.category_id=c.id JOIN farmers f ON p.farmer_id=f.id JOIN users u ON f.user_id=u.id JOIN locations l ON p.location_id=l.id WHERE p.id=?", 'i', [(int)$_GET['view']]) : null;
$products = db_all("SELECT p.*, c.name AS category, u.full_name AS farmer, l.district FROM products p JOIN categories c ON p.category_id=c.id JOIN farmers f ON p.farmer_id=f.id JOIN users u ON f.user_id=u.id JOIN locations l ON p.location_id=l.id ORDER BY p.id DESC");
$farmers = db_all("SELECT f.id, u.full_name FROM farmers f JOIN users u ON f.user_id=u.id ORDER BY u.full_name");
$cats = db_all("SELECT * FROM categories ORDER BY name");
$page_title = 'Manage Products';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<?php if ($view): ?>
<div class="panel"><div class="panel-head"><h3><?= e($view['name']) ?></h3><a href="products.php">Close</a></div>
<table class="kv-table"><tr><th>Farmer</th><td><?= e($view['farmer']) ?></td></tr><tr><th>Category</th><td><?= e($view['category']) ?></td></tr><tr><th>Location</th><td><?= e($view['district']) ?></td></tr>
<tr><th>Quantity</th><td><?= number_format($view['quantity_kg']) ?> kg (<?= e($view['availability']) ?>)</td></tr><tr><th>Price</th><td><?= money($view['price_per_kg']) ?> / kg</td></tr><tr><th>Grade</th><td><?= grade_badge($view['quality_grade']) ?></td></tr>
<tr><th>Description</th><td><?= e($view['description']) ?></td></tr></table></div>
<?php endif; ?>
<?php if ($showForm): $v = $edit ?: $_POST; ?>
<div class="panel"><div class="panel-head"><h3><?= $edit ? 'Edit Product' : 'Add Product' ?></h3><a href="products.php">Cancel</a></div>
<?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="form-card wide flat" data-validate><?= csrf_field() ?>
<input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
<div class="form-grid">
<label>Product name <input type="text" name="name" required value="<?= e($v['name'] ?? '') ?>"></label>
<label>Farmer <select name="farmer_id" required><option value="">-- Select --</option><?php foreach ($farmers as $f): ?><option value="<?= $f['id'] ?>" <?= ($v['farmer_id'] ?? 0) == $f['id'] ? 'selected' : '' ?>><?= e($f['full_name']) ?></option><?php endforeach; ?></select></label>
<label>Category <select name="category_id" required><option value="">-- Select --</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($v['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
<label>Location <select name="location_id" required><?= location_options($v['location_id'] ?? 0) ?></select></label>
<label>Quantity (kg) <input type="number" name="quantity_kg" min="0" step="0.01" required value="<?= e($v['quantity_kg'] ?? '') ?>"></label>
<label>Price per kg (RWF) <input type="number" name="price_per_kg" min="1" step="0.01" required value="<?= e($v['price_per_kg'] ?? '') ?>"></label>
<label>Quality grade <select name="quality_grade"><?php foreach (['Pending','A','B','C'] as $g): ?><option <?= ($v['quality_grade'] ?? 'Pending') === $g ? 'selected' : '' ?>><?= $g ?></option><?php endforeach; ?></select></label>
<label>Image (optional) <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"></label>
</div><label>Description <textarea name="description" rows="3"><?= e($v['description'] ?? '') ?></textarea></label><button class="btn btn-lg">Save Product</button></form></div>
<?php endif; ?>
<div class="panel"><div class="panel-head"><h3><?= count($products) ?> products</h3><a href="?add=1" class="btn btn-sm">+ Add Product</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Product</th><th>Farmer</th><th>Category</th><th>Qty (kg)</th><th>Price</th><th>Grade</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($products as $p): ?><tr><td><?= $p['id'] ?></td><td><?= e($p['name']) ?></td><td><?= e($p['farmer']) ?></td><td><?= e($p['category']) ?></td><td><?= number_format($p['quantity_kg']) ?></td><td><?= number_format($p['price_per_kg']) ?></td><td><?= grade_badge($p['quality_grade']) ?></td><td><?= e($p['availability']) ?></td>
<td class="actions"><a class="btn btn-sm btn-outline" href="?view=<?= $p['id'] ?>">View</a><a class="btn btn-sm" href="?edit=<?= $p['id'] ?>">Edit</a>
<form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Delete this product?">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
