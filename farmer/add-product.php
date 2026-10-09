<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');
$f = current_farmer();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $cat = (int)($_POST['category_id'] ?? 0); $loc = (int)($_POST['location_id'] ?? 0);
    $qty = (float)($_POST['quantity_kg'] ?? 0); $price = (float)($_POST['price_per_kg'] ?? 0);
    $desc = trim($_POST['description'] ?? ''); $collect = trim($_POST['collection_point'] ?? '');
    if (strlen($name) < 3) $errors[] = 'Product name must have at least 3 characters.';
    if (!db_one("SELECT id FROM categories WHERE id=?", 'i', [$cat])) $errors[] = 'Select a category.';
    if (!$loc) $errors[] = 'Select a location.';
    if ($qty <= 0) $errors[] = 'Quantity must be greater than 0.';
    if ($price <= 0) $errors[] = 'Price must be greater than 0.';
    if ($collect === '') $errors[] = 'Enter a collection point.';
    $img = save_product_image($_FILES['image'] ?? []);
    if ($img === false) $errors[] = 'Image must be a JPG, PNG or WEBP file under 2 MB.';
    if (!$errors) {
        $availability = $qty < 500 ? 'Low Stock' : 'Available';
        $pid = db_exec("INSERT INTO products (farmer_id,category_id,location_id,name,description,quantity_kg,price_per_kg,image,quality_grade,availability) VALUES (?,?,?,?,?,?,?,?,'Pending',?)",
                       'iiissddss', [$f['id'], $cat, $loc, $name, $desc, $qty, $price, $img, $availability]);
        db_exec("INSERT INTO supply_chain (product_id,collection_point,current_stage) VALUES (?,?,2)", 'is', [$pid, $collect]);
        notify_role('inspector', "New product \"$name\" is waiting for quality inspection.");
        notify_role('admin', "New product \"$name\" was added by " . $f['full_name'] . '.');
        flash('Product added. It will be graded by an inspector.');
        redirect('farmer/products.php');
    }
}
$cats = db_all("SELECT * FROM categories ORDER BY name");
$page_title = 'Add Product';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="panel"><?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="form-card wide flat" data-validate>
  <?= csrf_field() ?>
  <div class="form-grid">
    <label>Product name <input type="text" name="name" required minlength="3" value="<?= e($_POST['name'] ?? '') ?>"></label>
    <label>Category <select name="category_id" required><option value="">-- Select --</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($_POST['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
    <label>Product location <select name="location_id" required><?= location_options($_POST['location_id'] ?? $f['location_id']) ?></select></label>
    <label>Collection point <input type="text" name="collection_point" required placeholder="e.g. Musanze Collection Center" value="<?= e($_POST['collection_point'] ?? '') ?>"></label>
    <label>Quantity available (kg) <input type="number" name="quantity_kg" min="1" step="0.01" required value="<?= e($_POST['quantity_kg'] ?? '') ?>"></label>
    <label>Price per kg (RWF) <input type="number" name="price_per_kg" min="1" step="0.01" required value="<?= e($_POST['price_per_kg'] ?? '') ?>"></label>
    <label class="full">Product image (optional, JPG/PNG, max 2 MB) <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"></label>
  </div>
  <label>Description <textarea name="description" rows="3" maxlength="500"><?= e($_POST['description'] ?? '') ?></textarea></label>
  <button class="btn btn-lg" type="submit">Save Product</button>
</form></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
