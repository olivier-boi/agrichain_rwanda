<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');
$f = current_farmer();
$records = db_all("SELECT s.*, p.name AS product FROM supply_chain s JOIN products p ON s.product_id=p.id WHERE p.farmer_id=? ORDER BY s.updated_at DESC", 'i', [$f['id']]);
$page_title = 'Supply Chain Status';
require_once __DIR__ . '/../includes/dash_header.php';
foreach ($records as $r): ?>
<div class="chain-card"><div class="chain-head"><div><h3><?= e($r['product']) ?> <small>#<?= $r['id'] ?></small></h3><p>Collection point: <?= e($r['collection_point']) ?></p></div></div>
<?= timeline_html($r['current_stage']) ?>
<div class="chain-meta"><span>🔍 Inspection: <b><?= e($r['inspection_status']) ?></b></span><span>🏬 Storage: <b><?= e($r['storage_status']) ?></b></span><span>🚚 Transport: <b><?= e($r['transportation_status']) ?></b></span><span>📍 Destination: <b><?= e($r['destination'] ?: 'Not assigned') ?></b></span></div></div>
<?php endforeach; if (!$records): ?><p class="empty-msg">No supply-chain records yet.</p><?php endif;
require_once __DIR__ . '/../includes/dash_footer.php'; ?>
