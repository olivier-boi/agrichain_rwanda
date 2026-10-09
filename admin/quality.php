<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)$_POST['id'];
    $qi = db_one("SELECT product_id FROM quality_inspections WHERE id=?", 'i', [$id]);
    if ($qi) {
        db_exec("DELETE FROM quality_inspections WHERE id=?", 'i', [$id]);
        $last = db_one("SELECT final_grade FROM quality_inspections WHERE product_id=? ORDER BY inspection_date DESC, id DESC LIMIT 1", 'i', [$qi['product_id']]);
        db_exec("UPDATE products SET quality_grade=? WHERE id=?", 'si', [$last['final_grade'] ?? 'Pending', $qi['product_id']]);   // product grade follows the latest inspection
        flash('Inspection deleted.');
    }
    redirect('admin/quality.php');
}
$view = isset($_GET['view']) ? db_one("SELECT qi.*, p.name AS product, u.full_name AS inspector FROM quality_inspections qi JOIN products p ON qi.product_id=p.id JOIN users u ON qi.inspector_id=u.id WHERE qi.id=?", 'i', [(int)$_GET['view']]) : null;
$list = db_all("SELECT qi.*, p.name AS product, u.full_name AS inspector, fu.full_name AS farmer FROM quality_inspections qi JOIN products p ON qi.product_id=p.id JOIN users u ON qi.inspector_id=u.id JOIN farmers f ON p.farmer_id=f.id JOIN users fu ON f.user_id=fu.id ORDER BY qi.inspection_date DESC, qi.id DESC");
$page_title = 'Quality Inspections';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<?php if ($view): ?>
<div class="panel"><div class="panel-head"><h3>Inspection #<?= $view['id'] ?> - <?= e($view['product']) ?></h3><a href="quality.php">Close</a></div>
<table class="kv-table"><tr><th>Inspector</th><td><?= e($view['inspector']) ?></td></tr><tr><th>Date</th><td><?= e($view['inspection_date']) ?></td></tr>
<tr><th>Appearance / Size / Cleanliness</th><td><?= $view['appearance_score'] ?> / <?= $view['size_score'] ?> / <?= $view['cleanliness_score'] ?></td></tr>
<tr><th>Moisture / Damage</th><td><?= $view['moisture_level'] ?>% / <?= $view['damage_percentage'] ?>%</td></tr><tr><th>Final grade</th><td><?= grade_badge($view['final_grade']) ?></td></tr><tr><th>Comments</th><td><?= e($view['comments']) ?></td></tr></table></div>
<?php endif; ?>
<div class="panel"><div class="panel-head"><h3><?= count($list) ?> inspections</h3><a class="btn btn-sm" href="<?= url('quality.php#inspect') ?>">+ New Inspection</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Date</th><th>Product</th><th>Farmer</th><th>Inspector</th><th>Damage</th><th>Grade</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($list as $r): ?><tr><td><?= $r['id'] ?></td><td><?= e($r['inspection_date']) ?></td><td><?= e($r['product']) ?></td><td><?= e($r['farmer']) ?></td><td><?= e($r['inspector']) ?></td><td><?= $r['damage_percentage'] ?>%</td><td><?= grade_badge($r['final_grade']) ?></td>
<td class="actions"><a class="btn btn-sm btn-outline" href="?view=<?= $r['id'] ?>">View</a><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Delete this inspection?">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
