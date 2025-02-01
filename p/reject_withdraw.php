<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("UPDATE transactions SET status='rejected' WHERE id=$id");
    header("Location: admin.php");
}
?>
