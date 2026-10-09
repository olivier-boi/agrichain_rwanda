<?php
// buyer/place-order.php - receives the order form from product-details.php
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('marketplace.php');

$buyer = current_buyer();
$pid = (int)($_POST['product_id'] ?? 0);
$qty = (float)($_POST['quantity'] ?? 0);
$delivery = trim($_POST['delivery_location'] ?? '');
$back = 'product-details.php?id=' . $pid;

if ($qty <= 0)              { flash('Enter a valid quantity.', 'error'); redirect($back); }
if (strlen($delivery) < 4)  { flash('Enter a delivery location.', 'error'); redirect($back); }

$conn->begin_transaction();
try {
    // FOR UPDATE locks the row so two buyers cannot take the same stock at the same time
    $p = db_one("SELECT p.*, f.user_id AS farmer_user, f.farm_location FROM products p JOIN farmers f ON p.farmer_id=f.id WHERE p.id=? FOR UPDATE", 'i', [$pid]);
    if (!$p || $p['availability'] === 'Sold Out' || $p['quantity_kg'] <= 0) throw new Exception('This product is not available.');
    if ($qty > $p['quantity_kg'])  throw new Exception('Only ' . number_format($p['quantity_kg']) . ' kg are available.');

    $total = $qty * $p['price_per_kg'];                                   // Total = Quantity x Price
    $oid = db_exec("INSERT INTO orders (buyer_id, delivery_location, total_amount, status) VALUES (?,?,?,'Pending')", 'isd', [$buyer['id'], $delivery, $total]);
    db_exec("INSERT INTO order_items (order_id, product_id, quantity_kg, unit_price, subtotal) VALUES (?,?,?,?,?)", 'iiddd', [$oid, $pid, $qty, $p['price_per_kg'], $total]);
    db_exec("UPDATE products SET quantity_kg = quantity_kg - ? WHERE id=?", 'di', [$qty, $pid]);
    refresh_availability($pid);

    $last = db_one("SELECT collection_point FROM supply_chain WHERE product_id=? ORDER BY id DESC LIMIT 1", 'i', [$pid]);
    db_exec("INSERT INTO supply_chain (product_id, order_id, collection_point, inspection_status, destination, current_stage) VALUES (?,?,?,?,?,2)",
            'iisss', [$pid, $oid, $last['collection_point'] ?? $p['farm_location'], $p['quality_grade'] === 'Pending' ? 'Pending' : 'Completed', $delivery]);

    notify($p['farmer_user'], "New order #$oid for {$p['name']} (" . number_format($qty) . " kg).");
    notify(user()['id'], "Your order #$oid was placed. Total: " . money($total));
    $conn->commit();
    flash("Order #$oid placed successfully! Total: " . money($total));
    redirect('buyer/orders.php');
} catch (Exception $ex) {
    $conn->rollback();
    flash($ex->getMessage(), 'error');
    redirect($back);
}
