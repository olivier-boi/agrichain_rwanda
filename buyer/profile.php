<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');
$b = current_buyer();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST; $d['email'] = $b['email']; $d['full_name'] = $d['contact_person'] ?? '';
    $errors = validate_account($d, false);
    if (strlen(trim($d['company_name'] ?? '')) < 3) $errors[] = 'Enter the company name.';
    $newpw = $d['new_password'] ?? '';
    if ($newpw !== '' && strlen($newpw) < 6) $errors[] = 'New password must have at least 6 characters.';
    if (!$errors) {
        db_exec("UPDATE users SET full_name=?, phone=? WHERE id=?", 'ssi', [trim($d['contact_person']), trim($d['phone']), user()['id']]);
        db_exec("UPDATE buyers SET location_id=?, company_name=?, contact_person=?, business_type=? WHERE id=?", 'isssi',
                [(int)$d['location_id'], trim($d['company_name']), trim($d['contact_person']), trim($d['business_type']), $b['id']]);
        if ($newpw !== '') db_exec("UPDATE users SET password=? WHERE id=?", 'si', [password_hash($newpw, PASSWORD_DEFAULT), user()['id']]);
        $_SESSION['user']['name'] = trim($d['contact_person']);
        flash('Profile updated.');
        redirect('buyer/profile.php');
    }
}
$page_title = 'My Profile';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="panel"><?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
<form method="post" class="form-card wide flat" data-validate><?= csrf_field() ?>
<div class="form-grid">
  <label>Company name <input type="text" name="company_name" required value="<?= e($b['company_name']) ?>"></label>
  <label>Contact person <input type="text" name="contact_person" required value="<?= e($b['contact_person']) ?>"></label>
  <label>Email (cannot change) <input type="email" value="<?= e($b['email']) ?>" readonly></label>
  <label>Phone <input type="tel" name="phone" required pattern="07[2389][0-9]{7}" value="<?= e($b['phone']) ?>"></label>
  <label>Location <select name="location_id" required><?= location_options($b['location_id']) ?></select></label>
  <label>Business type <input type="text" name="business_type" required value="<?= e($b['business_type']) ?>"></label>
  <label class="full">New password (leave empty to keep current) <input type="password" name="new_password" minlength="6"></label>
</div><button class="btn btn-lg">Save Profile</button></form></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
