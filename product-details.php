<?php
require_once __DIR__ . '/includes/auth.php';
$id = (int)($_GET['id'] ?? 0);
$p = db_one("SELECT p.*, c.name AS category, u.full_name AS farmer, u.phone AS farmer_phone, l.district, l.sector
             FROM products p JOIN categories c ON p.category_id=c.id
             JOIN farmers f ON p.farmer_id=f.id JOIN users u ON f.user_id=u.id
             JOIN locations l ON p.location_id=l.id WHERE p.id=?", 'i', [$id]);
if (!$p) { flash('Product not found.', 'error'); redirect('marketplace.php'); }

$insp = db_one("SELECT qi.*, u.full_name AS inspector FROM quality_inspections qi JOIN users u ON qi.inspector_id=u.id
                WHERE qi.product_id=? ORDER BY qi.inspection_date DESC, qi.id DESC LIMIT 1", 'i', [$id]);
$chain = db_one("SELECT * FROM supply_chain WHERE product_id=? ORDER BY id DESC LIMIT 1", 'i', [$id]);
$buyer = has_role('buyer') ? current_buyer() : null;

$page_title = $p['name']; $active = 'marketplace';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-banner small"><div class="container"><h1><?= e($p['name']) ?></h1><p><a href="<?= url('marketplace.php') ?>">← Back to Marketplace</a></p></div></section>
<section class="section"><div class="container detail-grid">
  <div class="detail-img"><?= product_visual($p) ?></div>
  <div class="detail-info">
    <span class="pc-cat"><?= e($p['category']) ?></span>
    <h2><?= e($p['name']) ?> <?= grade_badge($p['quality_grade']) ?></h2>
    <p><?= nl2br(e($p['description'])) ?></p>
    <table class="kv-table">
      <tr><th>Farmer</th><td><?= e($p['farmer']) ?></td></tr>
      <tr><th>Location</th><td><?= e($p['district'] . ' - ' . $p['sector']) ?></td></tr>
      <tr><th>Available quantity</th><td><?= number_format($p['quantity_kg']) ?> kg (<?= e($p['availability']) ?>)</td></tr>
      <tr><th>Price</th><td><b><?= number_format($p['price_per_kg']) ?> RWF</b> per kg</td></tr>
      <tr><th>Quality grade</th><td><?= grade_badge($p['quality_grade']) ?></td></tr>
      <tr><th>Inspection date</th><td><?= $insp ? e($insp['inspection_date']) : 'Not yet inspected' ?></td></tr>
      <tr><th>Quality inspector</th><td><?= $insp ? e($insp['inspector']) : '-' ?></td></tr>
      <tr><th>Supply-chain status</th><td><?= $chain ? e(chain_stages()[$chain['current_stage'] - 1]) : 'Not started' ?></td></tr>
    </table>
    <?php if ($insp): ?><p class="note"><b>Inspector comment:</b> <?= e($insp['comments']) ?></p><?php endif; ?>

    <?php if ($p['availability'] === 'Sold Out' || $p['quantity_kg'] <= 0): ?>
      <p class="alert alert-error">This product is sold out.</p>
    <?php elseif ($buyer): ?>
      <form method="post" action="<?= url('buyer/place-order.php') ?>" class="order-box" data-validate>
        <h3>Place Order</h3>
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
        <label>Quantity (kg) <input type="number" name="quantity" id="qty" min="1" max="<?= (int)$p['quantity_kg'] ?>" step="1" required></label>
        <label>Delivery location <input type="text" name="delivery_location" required minlength="4" placeholder="e.g. Kimironko Market, Kigali"></label>
        <div class="total-box">Price: <span id="unitPrice" data-price="<?= (float)$p['price_per_kg'] ?>"><?= number_format($p['price_per_kg']) ?></span> RWF/kg &nbsp;|&nbsp; Total: <b><span id="orderTotal">0</span> RWF</b></div>
        <button class="btn btn-block" type="submit" data-confirm="Place this order?">Place Order</button>
      </form>
    <?php elseif (!is_logged_in()): ?>
      <p class="alert alert-info">Please <a href="<?= url('login.php') ?>"><b>log in</b></a> or <a href="<?= url('register.php?role=buyer') ?>"><b>register as a buyer</b></a> to place an order.</p>
    <?php else: ?>
      <p class="alert alert-info">Only buyer accounts can place orders.</p>
    <?php endif; ?>
  </div>
</div></section>
<?php if ($chain): ?>
<section class="section alt"><div class="container"><h2>Supply-Chain Progress</h2><?= timeline_html($chain['current_stage']) ?></div></section>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
