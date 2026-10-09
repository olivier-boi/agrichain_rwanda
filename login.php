<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) redirect(dashboard_url(user()['role']));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); $pass = $_POST['password'] ?? '';
    $u = db_one("SELECT * FROM users WHERE email=?", 's', [$email]);
    if ($u && password_verify($pass, $u['password'])) {
        if ($u['status'] !== 'active') {
            $error = 'Your account is inactive. Contact the administrator.';
        } else {
            session_regenerate_id(true);                                   // prevents session fixation
            $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['full_name'], 'email' => $u['email'], 'role' => $u['role']];
            flash('Welcome back, ' . $u['full_name'] . '!', 'success');
            redirect(dashboard_url($u['role']));
        }
    } else {
        $error = 'Wrong email or password.';
    }
}
$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap"><div class="form-card auth-card">
  <h2>Welcome back 👋</h2><p class="muted">Log in to your AgriChain account.</p>
  <?php if ($error): ?><p class="alert alert-error"><?= e($error) ?></p><?php endif; ?>
  <form method="post" data-validate>
    <?= csrf_field() ?>
    <label>Email <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
    <label>Password <input type="password" name="password" required></label>
    <button class="btn btn-block btn-lg" type="submit">Login</button>
  </form>
  <p class="center mt">No account? <a href="<?= url('register.php') ?>"><b>Register here</b></a></p>
  <p class="demo-box"><b>Demo accounts</b> (password: <code>password</code>)<br>admin@agrichain.rw · habimana@agrichain.rw (farmer)<br>kigalifresh@agrichain.rw (buyer) · inspector@agrichain.rw</p></div>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
