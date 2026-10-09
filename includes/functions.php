<?php
// includes/functions.php - small helper functions used everywhere.
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/* ---------- Database helpers (all use PREPARED STATEMENTS) ---------- */
// Run a SELECT and return all rows as an array.
function db_all($sql, $types = '', $params = []) {
    global $conn;
    $st = $conn->prepare($sql);
    if ($types !== '') { $st->bind_param($types, ...$params); }
    $st->execute();
    $res = $st->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
// Run a SELECT and return only the first row (or null).
function db_one($sql, $types = '', $params = []) {
    $rows = db_all($sql, $types, $params);
    return $rows[0] ?? null;
}
// Run INSERT / UPDATE / DELETE. Returns the new id (INSERT) or affected rows.
function db_exec($sql, $types = '', $params = []) {
    global $conn;
    $st = $conn->prepare($sql);
    if ($types !== '') { $st->bind_param($types, ...$params); }
    $st->execute();
    return $st->insert_id > 0 ? $st->insert_id : $st->affected_rows;
}
function db_count($sql, $types = '', $params = []) {
    $r = db_one($sql, $types, $params);
    return $r ? (int)array_values($r)[0] : 0;
}

/* ---------- Output / navigation helpers ---------- */
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }   // protects against XSS
function url($path = '') { return BASE_URL . ltrim($path, '/'); }
function redirect($path) { header('Location: ' . url($path)); exit; }
function money($n) { return number_format((float)$n, 0, '.', ',') . ' RWF'; }
function flash($msg, $type = 'success') { $_SESSION['flash'] = ['msg' => $msg, 'type' => $type]; }
function get_flash() { if (isset($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; } return null; }

/* ---------- CSRF protection: every POST form must contain csrf_field() ---------- */
function csrf_token() { if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); } return $_SESSION['csrf']; }
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        die('Security token invalid. Please go back, reload the page and try again.');
    }
}

/* ---------- Quality grading ---------- */
// Scores are out of 10. Rule used by both PHP and JavaScript:
//   A = average score >= 8 and damage <= 5%
//   B = average score >= 6 and damage <= 12%
//   C = everything else
function calc_grade($appearance, $size, $clean, $damage) {
    $avg = ($appearance + $size + $clean) / 3;
    if ($avg >= 8 && $damage <= 5)  return 'A';
    if ($avg >= 6 && $damage <= 12) return 'B';
    return 'C';
}
function grade_badge($g) {
    if ($g === 'Pending') return '<span class="badge grade-pending">Pending</span>';
    return '<span class="badge grade-' . strtolower($g) . '">Grade ' . e($g) . '</span>';
}
function status_badge($s) {
    return '<span class="badge status-' . strtolower(str_replace(' ', '-', $s)) . '">' . e($s) . '</span>';
}

/* ---------- Products ---------- */
function cat_emoji($cat) {
    $m = ['Tubers'=>'🥔','Cereals'=>'🌽','Cash Crops'=>'☕','Legumes'=>'🫘','Vegetables'=>'🍅','Fruits'=>'🍌'];
    return $m[$cat] ?? '🌱';
}
// Shows the real photo if it exists in images/products/, otherwise a nice placeholder.
function product_visual($p) {
    $file = basename($p['image'] ?? '');
    if ($file !== '' && file_exists(__DIR__ . '/../images/products/' . $file)) {
        return '<img src="' . url('images/products/' . rawurlencode($file)) . '" alt="' . e($p['name']) . '" loading="lazy">';
    }
    return '<div class="img-placeholder"><span>' . cat_emoji($p['category'] ?? '') . '</span></div>';
}
function refresh_availability($pid) {
    $p = db_one("SELECT quantity_kg FROM products WHERE id=?", 'i', [$pid]);
    if (!$p) return;
    $a = $p['quantity_kg'] <= 0 ? 'Sold Out' : ($p['quantity_kg'] < 500 ? 'Low Stock' : 'Available');
    db_exec("UPDATE products SET availability=? WHERE id=?", 'si', [$a, $pid]);
}
// Saves an uploaded product image. Returns file name, null (no file) or false (invalid).
function save_product_image($file) {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) return false;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) return false;
    if (@getimagesize($file['tmp_name']) === false) return false;           // must be a real image
    $name = 'p_' . bin2hex(random_bytes(6)) . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], __DIR__ . '/../images/products/' . $name) ? $name : false;
}

/* ---------- Locations ---------- */
function all_locations() { return db_all("SELECT * FROM locations ORDER BY district, sector"); }
function location_options($selected = 0) {
    $html = '<option value="">-- Select district / sector --</option>';
    foreach (all_locations() as $l) {
        $sel = ($l['id'] == $selected) ? ' selected' : '';
        $html .= '<option value="' . $l['id'] . '"' . $sel . '>' . e($l['district'] . ' - ' . $l['sector']) . '</option>';
    }
    return $html;
}

/* ---------- Notifications ---------- */
function notify($user_id, $message) {
    db_exec("INSERT INTO notifications (user_id, message) VALUES (?, ?)", 'is', [$user_id, $message]);
}
function notify_role($role, $message) {
    foreach (db_all("SELECT id FROM users WHERE role=?", 's', [$role]) as $u) notify($u['id'], $message);
}

/* ---------- Supply chain ---------- */
function chain_stages() { return ['Farmer', 'Collection', 'Quality Inspection', 'Storage', 'Transportation', 'Buyer', 'Delivery']; }
function timeline_html($stage) {
    $html = '<div class="timeline" data-stage="' . (int)$stage . '">';
    foreach (chain_stages() as $i => $name) {
        $n = $i + 1;
        $cls = $n < $stage ? 'done' : ($n == $stage ? 'current' : '');
        if ($stage >= 7 && $n == 7) $cls = 'done';
        $html .= '<div class="tl-step ' . $cls . '"><div class="tl-dot">' . ($cls === 'done' ? '✓' : $n) . '</div><div class="tl-label">' . $name . '</div></div>';
    }
    return $html . '</div>';
}

/* ---------- Orders ---------- */
// Changes an order status, keeps the supply chain in sync and notifies the buyer.
function update_order_status($oid, $status) {
    global $conn;
    $allowed = ['Pending','Confirmed','Processing','In Transit','Delivered','Cancelled'];
    $order = db_one("SELECT * FROM orders WHERE id=?", 'i', [$oid]);
    if (!$order || !in_array($status, $allowed, true)) return false;
    if ($order['status'] === $status) return true;
    if (in_array($order['status'], ['Cancelled', 'Delivered'], true)) return false;   // final states

    $conn->begin_transaction();
    db_exec("UPDATE orders SET status=? WHERE id=?", 'si', [$status, $oid]);

    if ($status === 'Cancelled') {                       // give the stock back
        foreach (db_all("SELECT product_id, quantity_kg FROM order_items WHERE order_id=?", 'i', [$oid]) as $it) {
            db_exec("UPDATE products SET quantity_kg = quantity_kg + ? WHERE id=?", 'di', [$it['quantity_kg'], $it['product_id']]);
            refresh_availability($it['product_id']);
        }
    } else {                                            // move the product along the supply chain
        $map = ['Pending' => 2, 'Confirmed' => 3, 'Processing' => 4, 'In Transit' => 5, 'Delivered' => 7];
        $st = $map[$status];
        $storage   = $st >= 5 ? 'Released' : ($st >= 4 ? 'Stored' : 'Pending');
        $transport = $st >= 7 ? 'Arrived' : ($st >= 5 ? 'In Transit' : 'Pending');
        $delivery  = $st >= 7 ? 'Delivered' : 'Pending';
        db_exec("UPDATE supply_chain SET current_stage=?, storage_status=?, transportation_status=?, delivery_status=? WHERE order_id=?",
                'isssi', [$st, $storage, $transport, $delivery, $oid]);
    }
    $b = db_one("SELECT b.user_id FROM orders o JOIN buyers b ON o.buyer_id=b.id WHERE o.id=?", 'i', [$oid]);
    if ($b) notify($b['user_id'], "Your order #$oid is now: $status.");
    $conn->commit();
    return true;
}

/* ---------- Accounts (used by register.php and the admin pages) ---------- */
function validate_account($d, $needPassword = true) {
    $err = [];
    if (strlen(trim($d['full_name'] ?? '')) < 3) $err[] = 'Name must have at least 3 characters.';
    if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL)) $err[] = 'Enter a valid email address.';
    if (!preg_match('/^07[2389][0-9]{7}$/', $d['phone'] ?? '')) $err[] = 'Phone must be 10 digits, e.g. 0788123456.';
    if ($needPassword && strlen($d['password'] ?? '') < 6) $err[] = 'Password must have at least 6 characters.';
    if (empty($d['location_id'])) $err[] = 'Please select a district / sector.';
    return $err;
}
function insert_user($d, $role) {
    return db_exec("INSERT INTO users (full_name,email,phone,password,role) VALUES (?,?,?,?,?)", 'sssss',
        [trim($d['full_name']), trim($d['email']), trim($d['phone']), password_hash($d['password'], PASSWORD_DEFAULT), $role]);
}
// Returns true on success or an error message string.
function create_farmer($d) {
    global $conn;
    $conn->begin_transaction();
    try {
        $uid = insert_user($d, 'farmer');
        db_exec("INSERT INTO farmers (user_id,location_id,farm_location,main_products,farm_size_ha) VALUES (?,?,?,?,?)", 'iissd',
            [$uid, (int)$d['location_id'], trim($d['farm_location']), trim($d['main_products']), (float)$d['farm_size_ha']]);
        $conn->commit();
        return true;
    } catch (mysqli_sql_exception $ex) {
        $conn->rollback();
        return $ex->getCode() == 1062 ? 'This email is already registered.' : 'Database error: ' . $ex->getMessage();
    }
}
function create_buyer($d) {
    global $conn;
    $conn->begin_transaction();
    try {
        $uid = insert_user($d, 'buyer');
        db_exec("INSERT INTO buyers (user_id,location_id,company_name,contact_person,business_type) VALUES (?,?,?,?,?)", 'iisss',
            [$uid, (int)$d['location_id'], trim($d['company_name']), trim($d['contact_person']), trim($d['business_type'])]);
        $conn->commit();
        return true;
    } catch (mysqli_sql_exception $ex) {
        $conn->rollback();
        return $ex->getCode() == 1062 ? 'This email is already registered.' : 'Database error: ' . $ex->getMessage();
    }
}
