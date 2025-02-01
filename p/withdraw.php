<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $esewa_number = $_POST['esewa_number'];
    $userId = $_SESSION['user_id'];

    // Fetch user balance
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    if (!$stmt) {
        die("Database Error: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();

    // Withdrawal validation
    if ($amount < 999) {
        $error = "Amount must be at least 999!";
    } elseif ($amount > $balance) {
        $error = "Insufficient balance!";
    } else {
        // ✅ INSERT QUERY WITHOUT `details`
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status) VALUES (?, ?, 'withdraw', 'pending')");
        if (!$stmt) {
            die("Database Error: " . $conn->error);
        }
        $stmt->bind_param("id", $userId, $amount);
        if ($stmt->execute()) {
            $success = "Withdrawal request sent for approval!";
        } else {
            $error = "Something went wrong!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Funds</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: url('assets/bg2.png') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            text-align: center;
        }
        h2 {
            color: #f9a825;
        }
        input[type="number"], input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #f9a825;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ffb300;
        }
        p {
            text-align: center;
            font-style: italic;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Withdraw Funds</h2>
        
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

        <form method="POST">
            <input type="number" name="amount" placeholder="Enter amount (min 999)" required>
            <input type="text" name="esewa_number" placeholder="Enter your eSewa number (Optional)" required>
            <button type="submit">Withdraw</button>
        </form>

        <p>"Money grows when managed wisely."</p>
        <a href="dashboard.php" style="color: #f9a825;">Back to Dashboard</a>
    </div>
</body>
</html>
