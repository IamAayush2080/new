<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];

    // ✅ Get withdrawal details
    $stmt = $conn->prepare("SELECT user_id, amount FROM transactions WHERE id = ? AND status = 'pending'");
    if (!$stmt) {
        die("Database Error: " . $conn->error);
    }
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $stmt->bind_result($user_id, $amount);
    if ($stmt->fetch()) {
        $stmt->close();

        // ✅ Check if user has enough balance
        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($balance);
        $stmt->fetch();
        $stmt->close();

        if ($balance >= $amount) {
            // ✅ Deduct amount from user's balance
            $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $user_id);
            $stmt->execute();
            $stmt->close();

            // ✅ Update transaction status
            $stmt = $conn->prepare("UPDATE transactions SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $transaction_id);
            if ($stmt->execute()) {
                header("Location: admin.php?success=Withdrawal approved");
            } else {
                die("Database Error: " . $conn->error);
            }
            $s
