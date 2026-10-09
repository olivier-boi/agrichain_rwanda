<?php
require_once __DIR__ . '/includes/auth.php';
$canInspect = has_role(['inspector', 'admin']);
$errors = [];

// ---- Save an inspection (inspector / admin only) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canInspect) {
    $pid = (int)($_POST['product_id'] ?? 0);
    $a = (int)($_POST['appearance'] ?? 0); $s = (int)($_POST['size'] ?? 0); $c = (int)($_POST['cleanliness'] ?? 0);
    $moist = (float)($_POST['moisture'] ?? -1); $dmg = (float)($_POST['damage'] ?? -1);
    $date = $_POST['inspection_date'] ?? '';
    $comments = trim($_POST['comments'] ?? '');
    $product = db_one("SELECT p.*, f.user_id AS farmer_user FROM products p JOIN farmers f ON p.farmer_id=f.id WHERE p.id=?", 'i', [$pid]);

    if (!$product) $errors[] = 'Select a valid product.';
    foreach ([$a, $s, $c] as $score) if ($score < 1 || $score > 10) { $errors[] = 'Scores must be between 1 and 10.'; break; }
    if ($moist < 0 || $moist > 100) $errors[] = 'Moisture must be between 0 and 100.';
    if ($dmg < 0 || $dmg > 100) $errors[] = 'Damage must be between 0 and 100.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $errors[] = 'Enter a valid inspection date.';

    if (!$errors) {
        $grade = calc_grade($a, $s, $c, $dmg);               // grade is ALWAYS calculated on the server
        db_exec("INSERT INTO quality_inspections (product_id,inspector_id,inspection_date,appearance_score,size_score,cleanliness_score,moisture_level,damage_percentage,final_grade,comments)
                 VALUES (?,?,?,?,?,?,?,?,?,?)", 'iisiiiddss', [$pid, user()['id'], $date, $a, $s, $c, $moist, $dmg, $grade, $comments]);
        db_exec("UPDATE products SET quality_grade=? WHERE id=?", 'si', [$grade, $pid]);
        db_exec("UPDATE supply_chain SET inspection_status='Completed', current_stage=GREATEST(current_stage,3) WHERE product_id=? AND current_stage<3", 'i', [$pid]);
        db_exec("UPDATE supply_chain SET inspection_status='Completed' WHERE product_id=?", 'i', [$pid]);
        notify($product['farmer_user'], 'Your product "' . $product['name'] . '" has been graded ' . $grade . '.');
        flash("Inspection saved. Final grade: $grade", 'success');
        redirect('quality.php#recent');
    }
}

$page_title = 'Quality Grading'; $active = 'quality';
require_once __DIR__ . '/includes/header.php';

$productsList = $canInspect ? db_all("SELECT p.id, p.name, u.full_name AS farmer FROM products p JOIN farmers f ON p.farmer_id=f.id JOIN users u ON f.user_id=u.id ORDER BY p.name") : [];
$recent = db_all("SELECT qi.*, p.name AS product, u.full_name AS inspector, fu.full_name AS farmer
                  FROM quality_inspections qi JOIN products p ON qi.product_id=p.id JOIN users u ON qi.inspector_id=u.id
                  JOIN farmers f ON p.farmer_id=f.id JOIN users fu ON f.user_id=fu.id ORDER BY qi.inspection_date DESC, qi.id DESC LIMIT 8");
?>
<section class="page-banner"><div class="container"><h1>Quality Grading</h1><p>Every product is inspected, scored and graded before it reaches buyers.</p></div></section>

<section class="section"><div class="container">
  <div class="section-head"><h2>Quality Grades</h2></div>
  <div class="grade-grid" id="gradeCards">
    <div class="grade-card grade-a-card" data-grade="A"><div class="grade-letter">A</div><h3>Grade A</h3><p>Premium quality agricultural products.</p><small>Average score 8+ and damage 5% or less</small></div>
    <div class="grade-card grade-b-card" data-grade="B"><div class="grade-letter">B</div><h3>Grade B</h3><p>Good quality with minor acceptable variations.</p><small>Average score 6+ and damage 12% or less</small></div>
    <div class="grade-card grade-c-card" data-grade="C"><div class="grade-letter">C</div><h3>Grade C</h3><p>Lower quality suitable for specific markets or processing.</p><small>Anything below Grade B</small></div>
  </div>
  <h3 class="mt">Quality Criteria</h3>
  <div class="tag-row">
    <?php foreach (['Appearance','Size','Weight','Moisture level','Cleanliness','Damage level','Freshness','Pest / disease condition'] as $t): ?><span class="tag"><?= $t ?></span><?php endforeach; ?>
  </div>
</div></section>

<section class="section alt" id="inspect"><div class="container">
  <div class="section-head"><h2>Quality Inspection Form</h2></div>
  <?php if (!$canInspect): ?>
    <p class="alert alert-info center">Only quality inspectors can submit inspections. <?php if (!is_logged_in()): ?><a href="<?= url('login.php') ?>"><b>Inspector login</b></a><?php endif; ?></p>
  <?php else: ?>
    <?php foreach ($errors as $er): ?><p class="alert alert-error"><?= e($er) ?></p><?php endforeach; ?>
    <form method="post" class="form-card wide" data-validate id="inspectForm">
      <?= csrf_field() ?>
      <div class="form-grid">
        <label>Product
          <select name="product_id" id="insProduct" required>
            <option value="">-- Select product --</option>
            <?php foreach ($productsList as $pl): ?><option value="<?= $pl['id'] ?>" data-farmer="<?= e($pl['farmer']) ?>"><?= e($pl['name'] . ' (#' . $pl['id'] . ')') ?></option><?php endforeach; ?>
          </select></label>
        <label>Farmer <input type="text" id="insFarmer" readonly placeholder="Selected automatically"></label>
        <label>Inspector <input type="text" value="<?= e(user()['name']) ?>" readonly></label>
        <label>Inspection date <input type="date" name="inspection_date" value="<?= date('Y-m-d') ?>" required></label>
        <label>Appearance score (1-10) <input type="number" name="appearance" id="sAppearance" min="1" max="10" required></label>
        <label>Size score (1-10) <input type="number" name="size" id="sSize" min="1" max="10" required></label>
        <label>Cleanliness score (1-10) <input type="number" name="cleanliness" id="sClean" min="1" max="10" required></label>
        <label>Moisture level (%) <input type="number" name="moisture" min="0" max="100" step="0.1" required></label>
        <label>Damage percentage (%) <input type="number" name="damage" id="sDamage" min="0" max="100" step="0.1" required></label>
        <div class="grade-result">Calculated final grade<div id="gradeResult" class="grade-big">-</div></div>
      </div>
      <label>Inspector comments <textarea name="comments" rows="3" maxlength="500" placeholder="Describe the condition of the product..."></textarea></label>
      <button class="btn btn-lg" type="submit">Save Inspection</button>
    </form>
  <?php endif; ?>
</div></section>

<section class="section" id="recent"><div class="container">
  <div class="section-head"><h2>Recent Inspections</h2></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Date</th><th>Product</th><th>Farmer</th><th>Inspector</th><th>Scores (A/S/C)</th><th>Damage</th><th>Grade</th></tr></thead>
    <tbody>
    <?php foreach ($recent as $r): ?>
      <tr><td><?= e($r['inspection_date']) ?></td><td><?= e($r['product']) ?></td><td><?= e($r['farmer']) ?></td><td><?= e($r['inspector']) ?></td>
          <td><?= $r['appearance_score'] ?> / <?= $r['size_score'] ?> / <?= $r['cleanliness_score'] ?></td><td><?= $r['damage_percentage'] ?>%</td><td><?= grade_badge($r['final_grade']) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
