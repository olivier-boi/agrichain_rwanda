<?php
require_once __DIR__ . '/includes/auth.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $msg = trim($_POST['message'] ?? '');
    if (strlen($name) < 3) $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
    if (strlen($msg) < 10) $errors[] = 'Message must have at least 10 characters.';
    if (!$errors) {
        // The message is delivered to every admin as a notification (no mail server needed in XAMPP).
        notify_role('admin', substr("Contact message from $name ($email): $msg", 0, 250));
        flash('Thank you! Your message has been sent.', 'success');
        redirect('contact.php');
    }
}
$page_title = 'Contact'; $active = 'contact';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-banner"><div class="container"><h1>Contact Us</h1><p>We would love to hear from you.</p></div></section>
<section class="section"><div class="container two-col">
  <div class="info-box"><h3>Get in touch</h3>
    <p>📍 Kigali, Rwanda</p><p>📧 info@agrichain.rw</p><p>📞 +250 791875132</p><p>🕒 Mon - Fri, 8:00 AM - 17:00 PM</p></div>
  <form method="ost" class="form-card" data-validate>
    <?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
    <?= csrf_field() ?>
    <label>Your name <input type="text" name="name" required minlength="3" value="<?= e($_POST['name'] ?? '') ?>"></label>
    <label>Email <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
    <label>Message <textarea name="message" rows="5" required minlength="10"><?= e($_POST['message'] ?? '') ?></textarea></label>
    <button class="btn btn-block" type="submit">Send Message</button>
  </form>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
