<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin', 'farmer', 'buyer', 'inspector']);
$uid = user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    db_exec("UPDATE notifications SET is_read=1 WHERE user_id=?", 'i', [$uid]);
    flash('All notifications marked as read.');
    redirect('notifications.php');
}
$list = db_all("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 50", 'i', [$uid]);
$page_title = 'Notifications'; $active = '';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-banner small"><div class="container"><h1>Notifications</h1></div></section>
<section class="section"><div class="container narrow">
  <form method="post"><?= csrf_field() ?><button class="btn btn-outline btn-sm">Mark all as read</button></form>
  <div class="notif-list mt">
    <?php foreach ($list as $n): ?>
      <div class="notif <?= $n['is_read'] ? '' : 'unread' ?>"><?= e($n['message']) ?><small><?= e($n['created_at']) ?></small></div>
    <?php endforeach; ?>
    <?php if (!$list): ?><p class="empty-msg">No notifications yet.</p><?php endif; ?>
  </div>
  <p class="mt"><a href="<?= url(dashboard_url(user()['role'])) ?>">← Back to dashboard</a></p>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
