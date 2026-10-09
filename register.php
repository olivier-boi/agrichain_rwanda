<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) redirect(dashboard_url(user()['role']));
$role = ($_POST['role'] ?? $_GET['role'] ?? 'farmer') === 'buyer' ? 'buyer' : 'farmer';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    if ($role === 'buyer') {
        $d['full_name'] = $d['contact_person'] ?? '';
        if (strlen(trim($d['company_name'] ?? '')) < 3) $errors[] = 'Enter the business / company name.';
        if (trim($d['business_type'] ?? '') === '') $errors[] = 'Select a business type.';
    } else {
        if (trim($d['main_products'] ?? '') === '') $errors[] = 'Enter your main agricultural products.';
        if (trim($d['farm_location'] ?? '') === '') $errors[] = 'Enter your farm location.';
        if (!is_numeric($d['farm_size_ha'] ?? '') || $d['farm_size_ha'] <= 0) $errors[] = 'Enter a valid farm size in hectares.';
    }
    $errors = array_merge($errors, validate_account($d));
    if (($d['password'] ?? '') !== ($d['confirm_password'] ?? '')) $errors[] = 'Passwords do not match.';
    if (!$errors) {
        $result = $role === 'buyer' ? create_buyer($d) : create_farmer($d);
        if ($result === true) { flash('Account created! You can now log in.', 'success'); redirect('login.php'); }
        $errors[] = $result;
    }
}
$old = fn($k) => e($_POST[$k] ?? '');
$page_title = 'Register';
require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap"><div class="form-card auth-card wide">
  <h2>Create your account</h2>
  <div class="role-tabs">
    <a href="?role=farmer" class="<?= $role === 'farmer' ? 'active' : '' ?>">👨🏾‍🌾 I'm a Farmer</a>
    <a href="?role=buyer" class="<?= $role === 'buyer' ? 'active' : '' ?>">🏪 I'm a Buyer</a>
  </div>
  <?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
  <form method="post" data-validate>
    <?= csrf_field() ?><input type="hidden" name="role" value="<?= $role ?>">
    <div class="form-grid">
    <?php if ($role === 'farmer'): ?>
      <label>Full name <input type="text" name="full_name" required minlength="3" value="<?= $old('full_name') ?>"></label>
      <label>Phone number <input type="tel" name="phone" required pattern="07[2389][0-9]{7}" placeholder="0788123456" value="<?= $old('phone') ?>"></label>
      <label>Email <input type="email" name="email" required value="<?= $old('email') ?>"></label>
      <label>District / Sector <select name="location_id" required><?= location_options($_POST['location_id'] ?? 0) ?></select></label>
      <label>Farm location <input type="text" name="farm_location" required placeholder="Village / cell" value="<?= $old('farm_location') ?>"></label>
      <label>Farm size (hectares) <input type="number" name="farm_size_ha" min="0.1" step="0.01" required value="<?= $old('farm_size_ha') ?>"></label>
      <label class="full">Main agricultural products <input type="text" name="main_products" required placeholder="e.g. Maize, Beans, Coffee" value="<?= $old('main_products') ?>"></label>
    <?php else: ?>
      <label>Business / company name <input type="text" name="company_name" required value="<?= $old('company_name') ?>"></label>
      <label>Contact person <input type="text" name="contact_person" required minlength="3" value="<?= $old('contact_person') ?>"></label>
      <label>Phone <input type="tel" name="phone" required pattern="07[2389][0-9]{7}" placeholder="0788123456" value="<?= $old('phone') ?>"></label>
      <label>Email <input type="email" name="email" required value="<?= $old('email') ?>"></label>
      <label>Location <select name="location_id" required><?= location_options($_POST['location_id'] ?? 0) ?></select></label>
      <label>Business type
        <select name="business_type" required><option value="">-- Select --</option>
          <?php foreach (['Wholesale Market','Hotel / Restaurant','Supermarket','Food Processor','Exporter','School / Institution'] as $bt): ?>
            <option <?= ($_POST['business_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option><?php endforeach; ?></select></label>
    <?php endif; ?>
      <label>Password <input type="password" name="password" id="pw1" required minlength="6"></label>
      <label>Confirm password <input type="password" name="confirm_password" id="pw2" required></label>
    </div>
    <button class="btn btn-block btn-lg" type="submit">Create Account</button>
  </form>
  <p class="center mt">Already registered? <a href="<?= url('login.php') ?>"><b>Login</b></a></p>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
