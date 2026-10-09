<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');
$f = current_farmer();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$p = db_one("SELECT * FROM products WHERE id=? AND farmer_id=?", 'ii', [$id, $f['id']]);   // only my own product
if (!$p) { flash('Product not found.', 'error'); redirect('farmer/products.php'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $cat = (int)($_POST['category_id'] ?? 0); $loc = (int)($_POST['location_id'] ?? 0);
    $qty = (float)($_POST['quantity_kg'] ?? -1); $price = (float)($_POST['price_per_kg'] ?? 0); $desc = trim($_POST['description'] ?? '');
    if (strlen($name) < 3) $errors[] = 'Product name must have at least 3 characters.';
    if (!$cat || !$loc) $errors[] = 'Select a category and a location.';
    if ($qty < 0) $errors[] = 'Quantity cannot be negative.';
    if ($price <= 0) $errors[] = 'Price must be greater than 0.';
    $img = save_product_image($_FILES['image'] ?? []);
    if ($img === false) $errors[] = 'Image must be a JPG, PNG or WEBP file under 2 MB.';
    if (!$errors) {
        db_exec("UPDATE products SET name=?, category_id=?, location_id=?, quantity_kg=?, price_per_kg=?, description=?, image=COALESCE(?, image) WHERE id=?",
                'siiddssi', [$name, $cat, $loc, $qty, $price, $desc, $img, $id]);
        refresh_availability($id);
        flash('Product updated.');
        redirect('farmer/products.php');
    }
}
$cats = db_all("SELECT * FROM categories ORDER BY name");
$page_title = 'Edit Product';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="panel"><?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="form-card wide flat" data-validate>
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
  <div class="form-grid">
    <label>Product name <input type="text" name="name" required value="<?= e($_POST['name'] ?? $p['name']) ?>"></label>
    <label>Category <select name="category_id" required><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id'] == $p['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
    <label>Location <select name="location_id" required><?= location_options($p['location_id']) ?></select></label>
    <label>Quantity (kg) <input type="number" name="quantity_kg" min="0" step="0.01" required value="<?= e($_POST['quantity_kg'] ?? $p['quantity_kg']) ?>"></label>
    <label>Price per kg (RWF) <input type="number" name="price_per_kg" min="1" step="0.01" required value="<?= e($_POST['price_per_kg'] ?? $p['price_per_kg']) ?>"></label>
    <label>New image (optional) <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"></label>
  </div>
  <label>Description <textarea name="description" rows="3"><?= e($_POST['description'] ?? $p['description']) ?></textarea></label>
  <button class="btn btn-lg" type="submit">Update Product</button> <a class="btn btn-outline btn-lg" href="products.php">Cancel</a>
</form></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
