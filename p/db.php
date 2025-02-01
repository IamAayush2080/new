<?php
$host = "localhost"; // Change if your database is hosted remotely
$user = "root"; // Your database username (default: root)
$password = ""; // Your database password (default: empty for XAMPP)
$dbname = "nep11_db"; // Your database name

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
