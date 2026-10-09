<?php
// test-db.php - TEMPORARY. Checks the database connection. Delete before the final presentation.
require_once __DIR__ . '/config/database.php';
echo "<h2 style='color:green'>✔ Connected to database: agrichain_rwanda</h2><table border='1' cellpadding='6'><tr><th>Table</th><th>Rows</th></tr>";
foreach (['users','farmers','buyers','categories','locations','products','quality_inspections','orders','order_items','supply_chain','notifications'] as $t) {
    $r = $conn->query("SELECT COUNT(*) AS n FROM $t")->fetch_assoc();
    echo "<tr><td>$t</td><td>{$r['n']}</td></tr>";
}
echo "</table><p><a href='index.php'>Open the website</a></p>";
