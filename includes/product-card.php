<?php /* One marketplace product card. Expects $p with: id,name,category,farmer,district,quantity_kg,price_per_kg,quality_grade,availability,image */ ?>
<div class="product-card <?= $p['availability'] === 'Sold Out' ? 'soldout' : '' ?>"
     data-name="<?= e(strtolower($p['name'] . ' ' . $p['farmer'])) ?>" data-category="<?= e($p['category']) ?>"
     data-location="<?= e($p['district']) ?>" data-grade="<?= e($p['quality_grade']) ?>" data-price="<?= (float)$p['price_per_kg'] ?>">
  <div class="pc-img"><?= product_visual($p) ?><span class="pc-grade"><?= grade_badge($p['quality_grade']) ?></span></div>
  <div class="pc-body">
    <span class="pc-cat"><?= e($p['category']) ?></span>
    <h3><?= e($p['name']) ?></h3>
    <p class="pc-meta">👨🏾‍🌾 <?= e($p['farmer']) ?><br>📍 <?= e($p['district']) ?> District</p>
    <div class="pc-row"><span><?= number_format($p['quantity_kg']) ?> kg</span><span class="avail avail-<?= strtolower(str_replace(' ', '-', $p['availability'])) ?>"><?= e($p['availability']) ?></span></div>
    <div class="pc-price"><?= number_format($p['price_per_kg']) ?> <small>RWF / kg</small></div>
    <a href="<?= url('product-details.php?id=' . (int)$p['id']) ?>" class="btn btn-block">View Details</a>
  </div>
</div>
