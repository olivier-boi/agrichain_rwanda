<?php
$page_title = 'Farmers'; $active = 'farmers';
require_once __DIR__ . '/includes/header.php';
$farmers = db_all("SELECT f.*, u.full_name, l.district, l.sector, (SELECT COUNT(*) FROM products p WHERE p.farmer_id=f.id) AS total_products
                   FROM farmers f JOIN users u ON f.user_id=u.id JOIN locations l ON f.location_id=l.id WHERE u.status='active' ORDER BY u.full_name");
?>
<section class="page-banner"><div class="container"><h1>Our Farmers</h1><p>Hard-working farmers across Rwanda's districts.</p></div></section>
<section class="section"><div class="container">
  <div class="card-grid">
    <?php foreach ($farmers as $f): ?>
      <div class="info-card person-card">
        <div class="avatar"><?= e(strtoupper(substr($f['full_name'], 0, 1))) ?></div>
        <h3><?= e($f['full_name']) ?></h3>
        <p>📍 <?= e($f['district'] . ', ' . $f['sector']) ?></p>
        <p>🌱 <?= e($f['main_products']) ?></p>
        <p>📐 <?= e($f['farm_size_ha']) ?> hectares &middot; <?= $f['total_products'] ?> products listed</p>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="cta-box"><h3>Are you a farmer?</h3><p>List your harvest and reach buyers across Rwanda.</p><a href="<?= url('register.php?role=farmer') ?>" class="btn">Register as Farmer</a></div>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
