<?php
session_start();
include 'db.php';

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete user
    $conn->query("DELETE FROM users WHERE id = $user_id");
    $conn->query("DELETE FROM transactions WHERE user_id = $user_id");

    // Redirect back to admin panel
    header("Location: admin.php");
    exit();
}
?>
