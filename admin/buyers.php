<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$errors = []; $showForm = isset($_GET['add']);
$edit = isset($_GET['edit']) ? db_one("SELECT b.*, u.email, u.phone FROM buyers b JOIN users u ON b.user_id=u.id WHERE b.id=?", 'i', [(int)$_GET['edit']]) : null;
if ($edit) $showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'add') {
        $d = $_POST; $d['full_name'] = $d['contact_person'] ?? '';
        $errors = validate_account($d);
        if (strlen(trim($d['company_name'] ?? '')) < 3 || trim($d['business_type'] ?? '') === '') $errors[] = 'Company name and business type are required.';
        if (!$errors) { $r = create_buyer($d); if ($r === true) { flash('Buyer added.'); redirect('admin/buyers.php'); } $errors[] = $r; }
        $showForm = true;
    } elseif ($act === 'edit') {
        $bid = (int)$_POST['id'];
        $row = db_one("SELECT b.user_id, u.email FROM buyers b JOIN users u ON b.user_id=u.id WHERE b.id=?", 'i', [$bid]);
        $d = array_merge($_POST, ['full_name' => $_POST['contact_person'] ?? '', 'email' => $row['email'] ?? 'x@x.rw']);
        $errors = validate_account($d, false);
        if (!$row) $errors[] = 'Buyer not found.';
        if (!$errors) {
            db_exec("UPDATE users SET full_name=?, phone=? WHERE id=?", 'ssi', [trim($d['contact_person']), trim($d['phone']), $row['user_id']]);
            db_exec("UPDATE buyers SET location_id=?, company_name=?, contact_person=?, business_type=? WHERE id=?", 'isssi',
                    [(int)$d['location_id'], trim($d['company_name']), trim($d['contact_person']), trim($d['business_type']), $bid]);
            flash('Buyer updated.'); redirect('admin/buyers.php');
        }
        $edit = db_one("SELECT b.*, u.email, u.phone FROM buyers b JOIN users u ON b.user_id=u.id WHERE b.id=?", 'i', [$bid]); $showForm = true;
    } elseif ($act === 'delete') {
        $bid = (int)$_POST['id'];
        $row = db_one("SELECT user_id FROM buyers WHERE id=?", 'i', [$bid]);
        if (!$row) flash('Buyer not found.', 'error');
        elseif (db_count("SELECT COUNT(*) FROM orders WHERE buyer_id=?", 'i', [$bid]) > 0) flash('This buyer has orders and cannot be deleted. Deactivate the account in Users instead.', 'error');
        else { db_exec("DELETE FROM users WHERE id=?", 'i', [$row['user_id']]); flash('Buyer deleted.'); }
        redirect('admin/buyers.php');
    }
}
$view = isset($_GET['view']) ? db_one("SELECT b.*, u.email, u.phone, u.status, l.district, l.sector FROM buyers b JOIN users u ON b.user_id=u.id JOIN locations l ON b.location_id=l.id WHERE b.id=?", 'i', [(int)$_GET['view']]) : null;
$buyers = db_all("SELECT b.*, u.email, u.phone, l.district, (SELECT COUNT(*) FROM orders o WHERE o.buyer_id=b.id) AS total_orders
                  FROM buyers b JOIN users u ON b.user_id=u.id JOIN locations l ON b.location_id=l.id ORDER BY b.id DESC");
$types = ['Wholesale Market','Hotel / Restaurant','Supermarket','Food Processor','Exporter','School / Institution'];
$page_title = 'Manage Buyers';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<?php if ($view): ?>
<div class="panel"><div class="panel-head"><h3>Buyer Details</h3><a href="buyers.php">Close</a></div>
<table class="kv-table"><tr><th>Company</th><td><?= e($view['company_name']) ?></td></tr><tr><th>Contact person</th><td><?= e($view['contact_person']) ?></td></tr>
<tr><th>Email / Phone</th><td><?= e($view['email']) ?> / <?= e($view['phone']) ?></td></tr><tr><th>Location</th><td><?= e($view['district'] . ' - ' . $view['sector']) ?></td></tr>
<tr><th>Business type</th><td><?= e($view['business_type']) ?></td></tr><tr><th>Account</th><td><?= e($view['status']) ?></td></tr></table></div>
<?php endif; ?>
<?php if ($showForm): $v = $edit ?: $_POST; ?>
<div class="panel"><div class="panel-head"><h3><?= $edit ? 'Edit Buyer' : 'Add Buyer' ?></h3><a href="buyers.php">Cancel</a></div>
<?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" class="form-card wide flat" data-validate><?= csrf_field() ?>
<input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>"><?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
<div class="form-grid">
<label>Company name <input type="text" name="company_name" required value="<?= e($v['company_name'] ?? '') ?>"></label>
<label>Contact person <input type="text" name="contact_person" required value="<?= e($v['contact_person'] ?? '') ?>"></label>
<label>Email <input type="email" name="email" required <?= $edit ? 'readonly' : '' ?> value="<?= e($v['email'] ?? '') ?>"></label>
<label>Phone <input type="tel" name="phone" required pattern="07[2389][0-9]{7}" value="<?= e($v['phone'] ?? '') ?>"></label>
<label>Location <select name="location_id" required><?= location_options($v['location_id'] ?? 0) ?></select></label>
<label>Business type <select name="business_type" required><?php foreach ($types as $t): ?><option <?= ($v['business_type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
<?php if (!$edit): ?><label>Password <input type="password" name="password" required minlength="6"></label><?php endif; ?>
</div><button class="btn btn-lg">Save Buyer</button></form></div>
<?php endif; ?>
<div class="panel"><div class="panel-head"><h3><?= count($buyers) ?> buyers</h3><a href="?add=1" class="btn btn-sm">+ Add Buyer</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Company</th><th>Contact</th><th>Phone</th><th>District</th><th>Type</th><th>Orders</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($buyers as $b): ?><tr><td><?= $b['id'] ?></td><td><?= e($b['company_name']) ?></td><td><?= e($b['contact_person']) ?><br><small><?= e($b['email']) ?></small></td><td><?= e($b['phone']) ?></td><td><?= e($b['district']) ?></td><td><?= e($b['business_type']) ?></td><td><?= $b['total_orders'] ?></td>
<td class="actions"><a class="btn btn-sm btn-outline" href="?view=<?= $b['id'] ?>">View</a><a class="btn btn-sm" href="?edit=<?= $b['id'] ?>">Edit</a>
<form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $b['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Delete this buyer?">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
