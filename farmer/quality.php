<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('farmer');
$f = current_farmer();
$reports = db_all("SELECT qi.*, p.name AS product, u.full_name AS inspector FROM quality_inspections qi JOIN products p ON qi.product_id=p.id JOIN users u ON qi.inspector_id=u.id
                   WHERE p.farmer_id=? ORDER BY qi.inspection_date DESC, qi.id DESC", 'i', [$f['id']]);
$page_title = 'Quality Reports';
require_once __DIR__ . '/../includes/dash_header.php';
?>
<div class="panel"><div class="table-wrap"><table class="table"><thead><tr><th>Date</th><th>Product</th><th>Inspector</th><th>Appearance</th><th>Size</th><th>Clean</th><th>Moisture</th><th>Damage</th><th>Grade</th><th>Comments</th></tr></thead><tbody>
<?php foreach ($reports as $r): ?>
<tr><td><?= e($r['inspection_date']) ?></td><td><?= e($r['product']) ?></td><td><?= e($r['inspector']) ?></td><td><?= $r['appearance_score'] ?>/10</td><td><?= $r['size_score'] ?>/10</td><td><?= $r['cleanliness_score'] ?>/10</td><td><?= $r['moisture_level'] ?>%</td><td><?= $r['damage_percentage'] ?>%</td><td><?= grade_badge($r['final_grade']) ?></td><td><?= e($r['comments']) ?></td></tr>
<?php endforeach; ?>
<?php if (!$reports): ?><tr><td colspan="10" class="empty-msg">No quality reports yet. Inspectors will grade your products soon.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
