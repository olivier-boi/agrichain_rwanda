<?php
$page_title = 'Buyers'; $active = 'buyers';
require_once __DIR__ . '/includes/header.php';
$buyers = db_all("SELECT b.company_name, b.business_type, l.district FROM buyers b JOIN users u ON b.user_id=u.id JOIN locations l ON b.location_id=l.id WHERE u.status='active' ORDER BY b.company_name");
?>
<section class="page-banner"><div class="container"><h1>For Buyers</h1><p>Source quality-graded produce from verified farmers.</p></div></section>
<section class="section"><div class="container">
  <div class="card-grid">
    <div class="info-card"><span class="ic-icon">🔎</span><h3>Discover</h3><p>Filter by crop, grade, district and price.</p></div>
    <div class="info-card"><span class="ic-icon">🛒</span><h3>Order Online</h3><p>Choose quantity and delivery place. The total is calculated for you.</p></div>
    <div class="info-card"><span class="ic-icon">📦</span><h3>Track Delivery</h3><p>Follow your order through the supply chain.</p></div>
  </div>
  <div class="section-head mt"><h2>Verified Buyers</h2></div>
  <div class="table-wrap"><table class="table"><thead><tr><th>Company</th><th>Business type</th><th>District</th></tr></thead><tbody>
    <?php foreach ($buyers as $b): ?><tr><td><?= e($b['company_name']) ?></td><td><?= e($b['business_type']) ?></td><td><?= e($b['district']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <div class="cta-box"><h3>Buy directly from farmers</h3><a href="<?= url('register.php?role=buyer') ?>" class="btn">Register as Buyer</a></div>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
