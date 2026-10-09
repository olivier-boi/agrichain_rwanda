<?php
// config/database.php - connects PHP to MySQL and defines the site URL.
$db_host = "localhost";
$db_user = "root";   // default XAMPP user
$db_pass = "";       // default XAMPP password is empty
$db_name = "agrichain_rwanda";

// If you use another folder name or port, change this line.
define('BASE_URL', '/agrichain-rwanda/');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $ex) {
    die("<h3>Database connection failed.</h3><p>Start MySQL in XAMPP and import database/agrichain_rwanda.sql.</p><small>" . htmlspecialchars($ex->getMessage()) . "</small>");
}
