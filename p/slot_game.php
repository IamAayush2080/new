<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT balance FROM users WHERE id='$user_id'");
$user = $result->fetch_assoc();
$balance = $user['balance'];

$symbols = ['🍒', '🍋', '🍉', '⭐', '💎', '🎰'];
$spin_result = [];
$win = false;
$message = "";

if (!isset($_SESSION['slot_count'])) {
    $_SESSION['slot_count'] = 0;
    $_SESSION['forced_slot_win'] = false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bet_amount = (int) $_POST['bet_amount'];

    if ($bet_amount < 20) {
        $message = "❌ Minimum bet is Rs. 20!";
    } elseif ($bet_amount > $balance) {
        $message = "❌ You don't have enough balance!";
    } else {
        $conn->query("UPDATE users SET balance = balance - $bet_amount WHERE id='$user_id'");

        $_SESSION['slot_count']++;

        // Generate slot results
        $spin_result = [$symbols[rand(0, 5)], $symbols[rand(0, 5)], $symbols[rand(0, 5)]];

        // Force win in first 2-3 spins
        if ($_SESSION['slot_count'] <= 3 && !$_SESSION['forced_slot_win']) {
            if ($_SESSION['slot_count'] == 3) {
                $spin_result = ['💎', '💎', '💎'];
                $_SESSION['forced_slot_win'] = true;
            } elseif (rand(1, 100) <= 40) {
                $spin_result = ['⭐', '⭐', '⭐'];
            }
        }

        // Check if user wins
        if ($spin_result[0] == $spin_result[1] && $spin_result[1] == $spin_result[2]) {
            $winnings = $bet_amount * 5;
            $conn->query("UPDATE users SET balance = balance + $winnings, total_winnings = total_winnings + $winnings WHERE id='$user_id'");
            $message = "🎉 Jackpot! You won Rs. $winnings!";
            $win = true;
        } else {
            $message = "❌ You lost Rs. $bet_amount. Try again!";
        }

        $result = $conn->query("SELECT balance FROM users WHERE id='$user_id'");
        $user = $result->fetch_assoc();
        $balance = $user['balance'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slot Machine - N£P 11</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            text-align: center;
            background: #222;
            color: white;
        }
        .container {
            margin: auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
        }
        .slot {
            font-size: 50px;
        }
        .btn {
            padding: 10px;
            background: gold;
            color: black;
            border: none;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🎰 Slot Machine</h2>
        <p>Your Balance: Rs. <strong><?php echo number_format($balance, 2); ?></strong></p>

        <div class="slot">
            <?php echo $spin_result ? implode(' ', $spin_result) : "🎰 🎰 🎰"; ?>
        </div>

        <?php if ($message): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="number" name="bet_amount" min="20" placeholder="Bet Amount" required>
            <button type="submit" class="btn">Spin 🎰</button>
        </form>

        <a href="games.php">⬅ Back</a>
    </div>
</body>
</html>
