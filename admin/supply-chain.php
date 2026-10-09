<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$errors = []; $showForm = isset($_GET['add']);
$edit = isset($_GET['edit']) ? db_one("SELECT * FROM supply_chain WHERE id=?", 'i', [(int)$_GET['edit']]) : null;
if ($edit) $showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'save') {
        $id = (int)($_POST['id'] ?? 0); $pid = (int)$_POST['product_id']; $oid = (int)($_POST['order_id'] ?? 0) ?: null;
        $collect = trim($_POST['collection_point'] ?? ''); $dest = trim($_POST['destination'] ?? '') ?: null;
        $insp = in_array($_POST['inspection_status'], ['Pending','Completed']) ? $_POST['inspection_status'] : 'Pending';
        $stor = in_array($_POST['storage_status'], ['Pending','Stored','Released']) ? $_POST['storage_status'] : 'Pending';
        $trans = in_array($_POST['transportation_status'], ['Pending','In Transit','Arrived']) ? $_POST['transportation_status'] : 'Pending';
        $deliv = in_array($_POST['delivery_status'], ['Pending','Delivered']) ? $_POST['delivery_status'] : 'Pending';
        $stage = (int)$_POST['current_stage'];
        if (!db_one("SELECT id FROM products WHERE id=?", 'i', [$pid])) $errors[] = 'Select a product.';
        if ($collect === '') $errors[] = 'Enter a collection point.';
        if ($stage < 1 || $stage > 7) $errors[] = 'Stage must be between 1 and 7.';
        if (!$errors) {
            if ($id) db_exec("UPDATE supply_chain SET product_id=?, order_id=?, collection_point=?, inspection_status=?, storage_status=?, transportation_status=?, destination=?, delivery_status=?, current_stage=? WHERE id=?",
                             'iissssssii', [$pid, $oid, $collect, $insp, $stor, $trans, $dest, $deliv, $stage, $id]);
            else db_exec("INSERT INTO supply_chain (product_id,order_id,collection_point,inspection_status,storage_status,transportation_status,destination,delivery_status,current_stage) VALUES (?,?,?,?,?,?,?,?,?)",
                         'iissssssi', [$pid, $oid, $collect, $insp, $stor, $trans, $dest, $deliv, $stage]);
            flash('Supply-chain record saved.'); redirect('admin/supply-chain.php');
        }
        $showForm = true; $edit = $id ? db_one("SELECT * FROM supply_chain WHERE id=?", 'i', [$id]) : null;
    } elseif ($act === 'delete') {
        db_exec("DELETE FROM supply_chain WHERE id=?", 'i', [(int)$_POST['id']]); flash('Record deleted.'); redirect('admin/supply-chain.php');
    }
}
$records = db_all("SELECT s.*, p.name AS product, u.full_name AS farmer FROM supply_chain s JOIN products p ON s.product_id=p.id JOIN farmers f ON p.farmer_id=f.id JOIN users u ON f.user_id=u.id ORDER BY s.id DESC");
$prods = db_all("SELECT id, name FROM products ORDER BY name");
$ords = db_all("SELECT id FROM orders ORDER BY id DESC");
$v = $edit ?: $_POST;
function sel($name, $opts, $cur) {
    $html = '<select name="' . $name . '">';
    foreach ($opts as $o) { $html .= '<option ' . ($o === $cur ? 'selected' : '') . '>' . $o . '</option>'; }
    return $html . '</select>';
}
$page_title = 'Supply Chain Records';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<?php if ($showForm): ?>
<div class="panel"><div class="panel-head"><h3><?= $edit ? 'Edit Record' : 'Add Record' ?></h3><a href="supply-chain.php">Cancel</a></div>
<?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" class="form-card wide flat" data-validate><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
<div class="form-grid">
<label>Product <select name="product_id" required><option value="">-- Select --</option><?php foreach ($prods as $p): ?><option value="<?= $p['id'] ?>" <?= ($v['product_id'] ?? 0) == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?> (#<?= $p['id'] ?>)</option><?php endforeach; ?></select></label>
<label>Order (optional) <select name="order_id"><option value="">None</option><?php foreach ($ords as $o): ?><option value="<?= $o['id'] ?>" <?= ($v['order_id'] ?? 0) == $o['id'] ? 'selected' : '' ?>>Order #<?= $o['id'] ?></option><?php endforeach; ?></select></label>
<label>Collection point <input type="text" name="collection_point" required value="<?= e($v['collection_point'] ?? '') ?>"></label>
<label>Destination <input type="text" name="destination" value="<?= e($v['destination'] ?? '') ?>"></label>
<label>Inspection status <?= sel('inspection_status', ['Pending','Completed'], $v['inspection_status'] ?? 'Pending') ?></label>
<label>Storage status <?= sel('storage_status', ['Pending','Stored','Released'], $v['storage_status'] ?? 'Pending') ?></label>
<label>Transportation status <?= sel('transportation_status', ['Pending','In Transit','Arrived'], $v['transportation_status'] ?? 'Pending') ?></label>
<label>Delivery status <?= sel('delivery_status', ['Pending','Delivered'], $v['delivery_status'] ?? 'Pending') ?></label>
<label>Current stage (1-7) <input type="number" name="current_stage" min="1" max="7" required value="<?= e($v['current_stage'] ?? 1) ?>"></label>
</div><p class="muted">1 Farmer · 2 Collection · 3 Quality Inspection · 4 Storage · 5 Transportation · 6 Buyer · 7 Delivery</p><button class="btn btn-lg">Save Record</button></form></div>
<?php endif; ?>
<div class="panel"><div class="panel-head"><h3><?= count($records) ?> records</h3><a href="?add=1" class="btn btn-sm">+ Add Record</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Product</th><th>Farmer</th><th>Collection</th><th>Inspection</th><th>Storage</th><th>Transport</th><th>Destination</th><th>Delivery</th><th>Stage</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($records as $r): ?><tr><td><?= $r['id'] ?></td><td><?= e($r['product']) ?></td><td><?= e($r['farmer']) ?></td><td><?= e($r['collection_point']) ?></td><td><?= e($r['inspection_status']) ?></td><td><?= e($r['storage_status']) ?></td><td><?= e($r['transportation_status']) ?></td><td><?= e($r['destination'] ?: '-') ?></td><td><?= e($r['delivery_status']) ?></td>
<td><?= $r['current_stage'] ?>/7<br><small><?= e(chain_stages()[$r['current_stage'] - 1]) ?></small></td><td><?= e(substr($r['updated_at'], 0, 16)) ?></td>
<td class="actions"><a class="btn btn-sm" href="?edit=<?= $r['id'] ?>">Edit</a><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Delete this record?">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
