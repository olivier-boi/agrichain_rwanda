<?php
$page_title = 'Marketplace'; $active = 'marketplace';
require_once __DIR__ . '/includes/header.php';

$products = db_all("SELECT p.*, c.name AS category, u.full_name AS farmer, l.district
                    FROM products p JOIN categories c ON p.category_id=c.id
                    JOIN farmers f ON p.farmer_id=f.id JOIN users u ON f.user_id=u.id
                    JOIN locations l ON p.location_id=l.id ORDER BY p.id DESC");
$categories = db_all("SELECT name FROM categories ORDER BY name");
$districts  = db_all("SELECT DISTINCT district FROM locations ORDER BY district");
?>
<section class="page-banner"><div class="container"><h1>Marketplace</h1><p>Browse quality-graded agricultural products directly from farmers.</p></div></section>
<section class="section">
  <div class="container">
    <div class="filter-bar" id="filterBar">
      <input type="search" id="fSearch" placeholder="🔍 Search product or farmer...">
      <select id="fCategory"><option value="">All categories</option><?php foreach ($categories as $c): ?><option><?= e($c['name']) ?></option><?php endforeach; ?></select>
      <select id="fLocation"><option value="">All locations</option><?php foreach ($districts as $d): ?><option><?= e($d['district']) ?></option><?php endforeach; ?></select>
      <select id="fGrade"><option value="">All grades</option><option value="A">Grade A</option><option value="B">Grade B</option><option value="C">Grade C</option><option value="Pending">Not graded</option></select>
      <select id="fPrice"><option value="">Any price</option><option value="500">Up to 500 RWF/kg</option><option value="1000">Up to 1,000 RWF/kg</option><option value="3000">Up to 3,000 RWF/kg</option></select>
      <button type="button" class="btn btn-outline" id="fReset">Reset</button>
    </div>
    <p class="result-count"><span id="resultCount"><?= count($products) ?></span> products found</p>
    <div class="product-grid" id="productGrid">
      <?php foreach ($products as $p) include __DIR__ . '/includes/product-card.php'; ?>
    </div>
    <p class="empty-msg" id="emptyMsg" hidden>No products match your filters.</p>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
