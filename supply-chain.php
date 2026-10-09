<?php
$page_title = 'Supply Chain'; $active = 'supply';
require_once __DIR__ . '/includes/header.php';
$records = db_all("SELECT s.*, p.name AS product, u.full_name AS farmer
                   FROM supply_chain s JOIN products p ON s.product_id=p.id JOIN farmers f ON p.farmer_id=f.id JOIN users u ON f.user_id=u.id
                   ORDER BY s.updated_at DESC, s.id DESC");
?>
<section class="page-banner"><div class="container"><h1>Supply-Chain Tracking</h1><p>Farmer → Collection → Quality Inspection → Storage → Transportation → Buyer → Delivery</p></div></section>
<section class="section"><div class="container">
  <?php foreach ($records as $r): ?>
    <div class="chain-card">
      <div class="chain-head">
        <div><h3><?= e($r['product']) ?> <small>#<?= $r['id'] ?></small></h3><p>Farmer: <?= e($r['farmer']) ?> &middot; Collection: <?= e($r['collection_point']) ?></p></div>
        <div><?= status_badge($r['delivery_status'] === 'Delivered' ? 'Delivered' : 'In Transit') ?></div>
      </div>
      <?= timeline_html($r['current_stage']) ?>
      <div class="chain-meta">
        <span>🔍 Inspection: <b><?= e($r['inspection_status']) ?></b></span>
        <span>🏬 Storage: <b><?= e($r['storage_status']) ?></b></span>
        <span>🚚 Transport: <b><?= e($r['transportation_status']) ?></b></span>
        <span>📍 Destination: <b><?= e($r['destination'] ?: 'Not assigned') ?></b></span>
        <span>🕒 Updated: <b><?= e($r['updated_at']) ?></b></span>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$records): ?><p class="empty-msg">No supply-chain records yet.</p><?php endif; ?>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
