<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');
$f = current_farmer();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST; $d['email'] = $f['email'];
    $errors = validate_account($d, false);
    if (trim($d['farm_location'] ?? '') === '' || trim($d['main_products'] ?? '') === '') $errors[] = 'Farm location and main products are required.';
    $newpw = $d['new_password'] ?? '';
    if ($newpw !== '' && strlen($newpw) < 6) $errors[] = 'New password must have at least 6 characters.';
    if (!$errors) {
        db_exec("UPDATE users SET full_name=?, phone=? WHERE id=?", 'ssi', [trim($d['full_name']), trim($d['phone']), user()['id']]);
        db_exec("UPDATE farmers SET location_id=?, farm_location=?, main_products=?, farm_size_ha=? WHERE id=?", 'issdi',
                [(int)$d['location_id'], trim($d['farm_location']), trim($d['main_products']), (float)$d['farm_size_ha'], $f['id']]);
        if ($newpw !== '') db_exec("UPDATE users SET password=? WHERE id=?", 'si', [password_hash($newpw, PASSWORD_DEFAULT), user()['id']]);
        $_SESSION['user']['name'] = trim($d['full_name']);
        flash('Profile updated.');
        redirect('farmer/profile.php');
    }
}
$page_title = 'My Profile';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="panel"><?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" class="form-card wide flat" data-validate><?= csrf_field() ?>
<div class="form-grid">
  <label>Full name <input type="text" name="full_name" required value="<?= e($f['full_name']) ?>"></label>
  <label>Email (cannot change) <input type="email" value="<?= e($f['email']) ?>" readonly></label>
  <label>Phone <input type="tel" name="phone" required pattern="07[2389][0-9]{7}" value="<?= e($f['phone']) ?>"></label>
  <label>District / Sector <select name="location_id" required><?= location_options($f['location_id']) ?></select></label>
  <label>Farm location <input type="text" name="farm_location" required value="<?= e($f['farm_location']) ?>"></label>
  <label>Farm size (ha) <input type="number" name="farm_size_ha" step="0.01" min="0.1" required value="<?= e($f['farm_size_ha']) ?>"></label>
  <label class="full">Main products <input type="text" name="main_products" required value="<?= e($f['main_products']) ?>"></label>
  <label class="full">New password (leave empty to keep the current one) <input type="password" name="new_password" minlength="6"></label>
</div><button class="btn btn-lg">Save Profile</button></form></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
