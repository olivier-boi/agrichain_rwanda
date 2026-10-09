<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$errors = [];
$edit = isset($_GET['edit']) ? db_one("SELECT * FROM categories WHERE id=?", 'i', [(int)$_GET['edit']]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? ''); $desc = trim($_POST['description'] ?? '');
    try {
        if ($act === 'save') {
            if (strlen($name) < 3) { $errors[] = 'Category name must have at least 3 characters.'; }
            elseif ($id) { db_exec("UPDATE categories SET name=?, description=? WHERE id=?", 'ssi', [$name, $desc, $id]); flash('Category updated.'); redirect('admin/categories.php'); }
            else { db_exec("INSERT INTO categories (name, description) VALUES (?,?)", 'ss', [$name, $desc]); flash('Category added.'); redirect('admin/categories.php'); }
        } elseif ($act === 'delete') {
            if (db_count("SELECT COUNT(*) FROM products WHERE category_id=?", 'i', [$id]) > 0) flash('Category is used by products and cannot be deleted.', 'error');
            else { db_exec("DELETE FROM categories WHERE id=?", 'i', [$id]); flash('Category deleted.'); }
            redirect('admin/categories.php');
        }
    } catch (mysqli_sql_exception $ex) { $errors[] = $ex->getCode() == 1062 ? 'That category already exists.' : $ex->getMessage(); }
}
$cats = db_all("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS total FROM categories c ORDER BY c.name");
$page_title = 'Categories';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="panel"><div class="panel-head"><h3><?= $edit ? 'Edit Category' : 'Add Category' ?></h3><?php if ($edit): ?><a href="categories.php">Cancel</a><?php endif; ?></div>
<?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" class="form-card wide flat" data-validate><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
<div class="form-grid"><label>Name <input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>"></label><label>Description <input type="text" name="description" value="<?= e($edit['description'] ?? '') ?>"></label></div>
<button class="btn">Save Category</button></form></div>
<div class="panel"><div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Products</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($cats as $c): ?><tr><td><?= $c['id'] ?></td><td><?= e($c['name']) ?></td><td><?= e($c['description']) ?></td><td><?= $c['total'] ?></td>
<td class="actions"><a class="btn btn-sm" href="?edit=<?= $c['id'] ?>">Edit</a><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Delete this category?">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
