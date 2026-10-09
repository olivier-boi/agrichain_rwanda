<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$errors = [];
$me = db_one("SELECT * FROM users WHERE id=?", 'i', [user()['id']]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST; $d['email'] = $me['email']; $d['location_id'] = 1;
    $errors = validate_account($d, false);
    $new = $d['new_password'] ?? '';
    if ($new !== '' && strlen($new) < 6) $errors[] = 'New password must have at least 6 characters.';
    if ($new !== '' && !password_verify($d['current_password'] ?? '', $me['password'])) $errors[] = 'Current password is wrong.';
    if (!$errors) {
        db_exec("UPDATE users SET full_name=?, phone=? WHERE id=?", 'ssi', [trim($d['full_name']), trim($d['phone']), $me['id']]);
        if ($new !== '') db_exec("UPDATE users SET password=? WHERE id=?", 'si', [password_hash($new, PASSWORD_DEFAULT), $me['id']]);
        $_SESSION['user']['name'] = trim($d['full_name']);
        flash('Settings saved.'); redirect('admin/settings.php');
    }
}
$page_title = 'Settings';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="dash-two">
<div class="panel"><h3>My account</h3>
<?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" class="form-card flat" data-validate><?= csrf_field() ?>
<label>Full name <input type="text" name="full_name" required value="<?= e($me['full_name']) ?>"></label>
<label>Email <input type="email" value="<?= e($me['email']) ?>" readonly></label>
<label>Phone <input type="tel" name="phone" required pattern="07[2389][0-9]{7}" value="<?= e($me['phone']) ?>"></label>
<label>Current password (only to change password) <input type="password" name="current_password"></label>
<label>New password <input type="password" name="new_password" minlength="6"></label>
<button class="btn">Save Settings</button></form></div>
<div class="panel"><h3>System information</h3><table class="kv-table">
<tr><th>Platform</th><td>AgriChain Rwanda 1.0</td></tr><tr><th>PHP version</th><td><?= e(PHP_VERSION) ?></td></tr><tr><th>Database</th><td>agrichain_rwanda (MySQL <?= e($conn->server_info) ?>)</td></tr>
<tr><th>Base URL</th><td><?= e(BASE_URL) ?></td></tr><tr><th>Grading rule</th><td>A: avg ≥ 8 &amp; damage ≤ 5% · B: avg ≥ 6 &amp; damage ≤ 12% · C: otherwise</td></tr></table></div>
</div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
