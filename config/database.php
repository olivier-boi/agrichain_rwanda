<?php
// Fetch credentials securely from Render's Environment
$host     = getenv('DB_HOST');
$port     = getenv('DB_PORT')     ?: '3306';
$user     = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname   = getenv('DB_NAME');

// Establish connection without crashing the page silently
$conn = @mysqli_connect($host, $user, $password, $dbname, $port);

// If it fails online, it will print a visible message instead of a blank screen
if (!$conn) {
    echo "<div style='color:red; padding:20px; background:#fff3f3;'>";
    echo "<strong>Production Database Connection Error:</strong> " . mysqli_connect_error();
    echo "</div>";
}
?>
