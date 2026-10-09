<?php
$page_title = 'About'; $active = 'about';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-banner"><div class="container"><h1>About AgriChain Rwanda</h1><p>Building trust between farmers and buyers through technology.</p></div></section>

<section class="section"><div class="container two-col">
  <div>
    <h2>Who We Are</h2>
    <p>AgriChain Rwanda is a smart agribusiness platform that connects farmers, suppliers, quality inspectors, buyers and administrators in one digital system. It records product information, quality grades, orders and every step of the supply chain.</p>
    <h2 class="mt">Our Mission</h2>
    <p>To give every Rwandan farmer fair access to trusted markets by making product quality, prices and supply-chain information transparent.</p>
    <h2 class="mt">Our Vision</h2>
    <p>A modern, efficient and traceable agricultural sector in Rwanda where quality is rewarded and no harvest is wasted.</p>
  </div>
  <div class="info-box"><h3>🎯 Objectives</h3>
    <ul class="check-list">
      <li>Connect farmers directly with buyers</li>
      <li>Record and publish verified quality grades</li>
      <li>Simplify ordering and payment totals</li>
      <li>Track products from farm to delivery</li>
      <li>Provide clear price and quality information</li>
      <li>Give administrators data for better decisions</li>
    </ul>
  </div>
</div></section>

<section class="section alt"><div class="container">
  <div class="section-head"><h2>The Problem</h2><p>Challenges facing agricultural supply chains today.</p></div>
  <div class="card-grid">
    <?php $problems = [['🚫','Limited market access','Small farmers often depend on a few local middlemen.'],
      ['❓','No transparent quality information','Buyers cannot easily verify the quality of what they buy.'],
      ['📞','Poor coordination','Farmers and buyers struggle to find and contact each other.'],
      ['⚙️','Supply-chain inefficiencies','Delays in collection, storage and transport cause losses.'],
      ['🔍','Difficult product tracking','It is hard to know where a product is at any moment.'],
      ['💰','Price and quality information gaps','Unclear prices lead to unfair trade and wasted harvests.']];
    foreach ($problems as $p): ?>
      <div class="info-card"><span class="ic-icon"><?= $p[0] ?></span><h3><?= $p[1] ?></h3><p><?= $p[2] ?></p></div>
    <?php endforeach; ?>
  </div>
</div></section>

<section class="section"><div class="container">
  <div class="section-head"><h2>Our Solution</h2><p>One platform that links the whole chain.</p></div>
  <div class="card-grid">
    <div class="info-card"><span class="ic-icon">🛒</span><h3>Digital Marketplace</h3><p>Farmers list products and buyers search by grade, location and price.</p></div>
    <div class="info-card"><span class="ic-icon">🏅</span><h3>Quality Grading</h3><p>Inspectors score products and the system calculates Grade A, B or C.</p></div>
    <div class="info-card"><span class="ic-icon">🚚</span><h3>Supply-Chain Tracking</h3><p>Each product moves through seven visible stages until delivery.</p></div>
  </div>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
