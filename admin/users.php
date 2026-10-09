<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$errors = []; $showForm = isset($_GET['add']);
$edit = isset($_GET['edit']) ? db_one("SELECT * FROM users WHERE id=?", 'i', [(int)$_GET['edit']]) : null;
if ($edit) $showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    if ($act === 'save') {
        $d = $_POST; $d['location_id'] = 1;             // location is not used for admin / inspector accounts
        $errors = validate_account($d, $id === 0);
        $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        if (!$errors) {
            try {
                if ($id) {
                    db_exec("UPDATE users SET full_name=?, phone=?, status=? WHERE id=?", 'sssi', [trim($d['full_name']), trim($d['phone']), $status, $id]);
                    if (($d['password'] ?? '') !== '' && strlen($d['password']) >= 6) db_exec("UPDATE users SET password=? WHERE id=?", 'si', [password_hash($d['password'], PASSWORD_DEFAULT), $id]);
                } else {
                    $role = in_array($d['role'] ?? '', ['admin', 'inspector']) ? $d['role'] : 'inspector';
                    insert_user($d, $role);
                }
                flash('User saved.'); redirect('admin/users.php');
            } catch (mysqli_sql_exception $ex) { $errors[] = $ex->getCode() == 1062 ? 'This email is already registered.' : $ex->getMessage(); }
        }
        $showForm = true; $edit = $id ? db_one("SELECT * FROM users WHERE id=?", 'i', [$id]) : null;
    } elseif ($act === 'toggle') {
        if ($id === user()['id']) flash('You cannot deactivate your own account.', 'error');
        else { db_exec("UPDATE users SET status=IF(status='active','inactive','active') WHERE id=?", 'i', [$id]); flash('Account status changed.'); }
        redirect('admin/users.php');
    } elseif ($act === 'delete') {
        $u = db_one("SELECT role FROM users WHERE id=?", 'i', [$id]);
        if ($id === user()['id']) flash('You cannot delete your own account.', 'error');
        elseif (!$u || !in_array($u['role'], ['admin', 'inspector'])) flash('Delete farmers and buyers from their own pages.', 'error');
        else {
            try { db_exec("DELETE FROM users WHERE id=?", 'i', [$id]); flash('User deleted.'); }
            catch (mysqli_sql_exception $ex) { flash('This user has inspection records and cannot be deleted. Deactivate the account instead.', 'error'); }
        }
        redirect('admin/users.php');
    }
}
$users = db_all("SELECT * FROM users ORDER BY id");
$v = $edit ?: $_POST;
$page_title = 'Users';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<?php if ($showForm): ?>
<div class="panel"><div class="panel-head"><h3><?= $edit ? 'Edit User' : 'Add Admin / Inspector' ?></h3><a href="users.php">Cancel</a></div>
<?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" class="form-card wide flat" data-validate><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
<div class="form-grid">
<label>Full name <input type="text" name="full_name" required value="<?= e($v['full_name'] ?? '') ?>"></label>
<label>Email <input type="email" name="email" required <?= $edit ? 'readonly' : '' ?> value="<?= e($v['email'] ?? '') ?>"></label>
<label>Phone <input type="tel" name="phone" required pattern="07[2389][0-9]{7}" value="<?= e($v['phone'] ?? '') ?>"></label>
<?php if (!$edit): ?><label>Role <select name="role"><option value="inspector">Quality Inspector</option><option value="admin">Administrator</option></select></label>
<?php else: ?><label>Status <select name="status"><option value="active" <?= $edit['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $edit['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></label><?php endif; ?>
<label class="full"><?= $edit ? 'New password (leave empty to keep)' : 'Password' ?> <input type="password" name="password" minlength="6" <?= $edit ? '' : 'required' ?>></label>
</div><button class="btn btn-lg">Save User</button></form></div>
<?php endif; ?>
<div class="panel"><div class="panel-head"><h3><?= count($users) ?> users</h3><a href="?add=1" class="btn btn-sm">+ Add Admin / Inspector</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($users as $u): ?><tr><td><?= $u['id'] ?></td><td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td><td><span class="badge role-<?= $u['role'] ?>"><?= e($u['role']) ?></span></td><td><?= status_badge($u['status'] === 'active' ? 'Confirmed' : 'Cancelled') ?></td><td><?= e(substr($u['created_at'], 0, 10)) ?></td>
<td class="actions"><a class="btn btn-sm" href="?edit=<?= $u['id'] ?>">Edit</a>
<form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $u['id'] ?>"><button class="btn btn-sm btn-outline" data-confirm="Change account status?"><?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button></form>
<form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $u['id'] ?>"><button class="btn btn-sm btn-danger" data-confirm="Delete this user?">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
