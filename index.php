<?php
$page_title = 'Home'; $active = 'index';
require_once __DIR__ . '/includes/header.php';

$stats = [
  ['Registered Farmers',     db_count("SELECT COUNT(*) FROM farmers"), '👨🏾‍🌾'],
  ['Agricultural Products',  db_count("SELECT COUNT(*) FROM products"), '🌽'],
  ['Verified Buyers',        db_count("SELECT COUNT(*) FROM buyers"), '🏪'],
  ['Completed Orders',       db_count("SELECT COUNT(*) FROM orders WHERE status='Delivered'"), '🚚'],
];
$featured = db_all("SELECT p.*, c.name AS category, u.full_name AS farmer, l.district
                    FROM products p JOIN categories c ON p.category_id=c.id
                    JOIN farmers f ON p.farmer_id=f.id JOIN users u ON f.user_id=u.id
                    JOIN locations l ON p.location_id=l.id
                    WHERE p.quality_grade IN ('A','B') ORDER BY p.created_at DESC, p.id DESC LIMIT 4");
$heroImg = file_exists(__DIR__ . '/images/backgrounds/hero.jpg') ? "style=\"background-image:linear-gradient(105deg,rgba(15,61,36,.93) 35%,rgba(15,61,36,.55)),url('" . url('images/backgrounds/hero.jpg') . "')\"" : '';
?>
<section class="hero" <?= $heroImg ?>>
  <div class="container hero-grid">
    <div class="hero-text">
      <span class="pill">🇷🇼 Made for Rwanda's agriculture</span>
      <h1>Connecting Rwanda’s Farmers to Better Markets</h1>
      <p>A smart digital platform for agricultural supply chains, quality grading and trusted market access.</p>
      <div class="hero-buttons">
        <a href="<?= url('marketplace.php') ?>" class="btn btn-gold btn-lg">Explore Marketplace</a>
        <a href="<?= url('register.php?role=farmer') ?>" class="btn btn-outline-light btn-lg">Register as Farmer</a>
      </div>
    </div>
    <div class="hero-card">
      <div class="hc-top"><span>🥔</span><div><b>Irish Potatoes</b><small>Musanze · Batch #AC-1021</small></div><?= grade_badge('A') ?></div>
      <ul class="hc-steps">
        <li class="done">Farmer registered</li><li class="done">Quality inspected</li>
        <li class="done">Stored &amp; verified</li><li class="now">In transit to Kigali</li>
      </ul>
      <div class="hc-bar"><div style="width:72%"></div></div>
      <small>Traceable from farm to buyer</small>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head"><h2>How AgriChain Works</h2><p>Six simple steps from the farm to the buyer's table.</p></div>
    <div class="steps">
      <?php $steps = [['Farmer registers','Create a free farmer account with farm details.'],
                      ['Farmer lists products','Add crops with quantity, price and location.'],
                      ['Inspector grades products','Certified inspectors score quality and assign Grade A, B or C.'],
                      ['Buyers discover products','Search and filter by grade, location and price.'],
                      ['Buyer places an order','Order online. The total is calculated instantly.'],
                      ['Product is delivered','Track every stage until it reaches the buyer.']];
      foreach ($steps as $i => $s): ?>
        <div class="step-card"><div class="step-num"><?= $i + 1 ?></div><h3><?= $s[0] ?></h3><p><?= $s[1] ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section stats-band">
  <div class="container stats-grid">
    <?php foreach ($stats as $s): ?>
      <div class="stat-card"><div class="stat-icon"><?= $s[2] ?></div><div class="stat-num" data-count="<?= $s[1] ?>">0</div><div class="stat-label"><?= $s[0] ?></div></div>
    <?php endforeach; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head"><h2>Fresh from Rwandan Farms</h2><p>Quality-graded products available now.</p></div>
    <div class="product-grid">
      <?php foreach ($featured as $p): include __DIR__ . '/includes/product-card.php'; endforeach; ?>
    </div>
    <p class="center mt"><a href="<?= url('marketplace.php') ?>" class="btn">View All Products</a></p>
  </div>
</section>

<section class="section alt">
  <div class="container feature-grid">
    <div class="feature"><span>✅</span><h3>Transparent Quality</h3><p>Every product carries an inspected grade, inspector name and date.</p></div>
    <div class="feature"><span>📍</span><h3>Full Traceability</h3><p>Follow a product from farm, collection and storage to delivery.</p></div>
    <div class="feature"><span>🤝</span><h3>Trusted Trade</h3><p>Verified farmers and buyers trading at clear, fair prices.</p></div>
  </div>
</section>

<section class="cta">
  <div class="container">
    <h2>Ready to grow your market?</h2>
    <p>Join farmers and buyers already using AgriChain Rwanda.</p>
    <a href="<?= url('register.php?role=farmer') ?>" class="btn btn-gold btn-lg">Register as Farmer</a>
    <a href="<?= url('register.php?role=buyer') ?>" class="btn btn-outline-light btn-lg">Register as Buyer</a>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
