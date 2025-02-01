<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user balance
$result = $conn->query("SELECT balance FROM users WHERE id='$user_id'");
$user = $result->fetch_assoc();
$balance = $user['balance'];

if (!isset($_SESSION['card_count'])) {
    $_SESSION['card_count'] = 0;
    $_SESSION['forced_card_win'] = false;
}

$showResult = false;
$user_card = "";
$ai_card = "";
$message = "";
$card_values = ["2" => 2, "3" => 3, "4" => 4, "5" => 5, "6" => 6, "7" => 7, "8" => 8, "9" => 9, "10" => 10, "J" => 11, "Q" => 12, "K" => 13, "A" => 14];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bet_amount = (int) $_POST['bet_amount'];

    if ($bet_amount < 20) {
        $message = "❌ Minimum bet amount is Rs. 20!";
    } elseif ($bet_amount > $balance) {
        $message = "❌ You don't have enough balance!";
    } else {
        $conn->query("UPDATE users SET balance = balance - $bet_amount WHERE id='$user_id'");

        $_SESSION['card_count']++;

        // Random card selection
        $cards = array_keys($card_values);
        $user_card = $cards[array_rand($cards)];
        $ai_card = $cards[array_rand($cards)];

        // Ensure user wins at least once in first 3 rounds
        if ($_SESSION['card_count'] <= 3 && !$_SESSION['forced_card_win']) {
            if ($_SESSION['card_count'] == 3) {
                $user_card = "A";
                $ai_card = "K";
                $_SESSION['forced_card_win'] = true;
            } elseif (rand(1, 100) <= 60) {
                $user_card = "J";
                $ai_card = "9";
            }
        }

        if ($card_values[$user_card] > $card_values[$ai_card]) {
            $winnings = $bet_amount * 2;
            $conn->query("UPDATE users SET balance = balance + $winnings WHERE id='$user_id'");
            $message = "🎉 You drew $user_card, AI drew $ai_card. You won Rs. $winnings!";
        } else {
            $message = "❌ You drew $user_card, AI drew $ai_card. You lost Rs. $bet_amount.";
        }

        $showResult = true;
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
    <title>Card Draw Game - N£P 11</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: linear-gradient(45deg, #1a1a2e, #16213e);
            color: white;
        }
        .container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .card {
            font-size: 30px;
            font-weight: bold;
            background: white;
            color: black;
            display: inline-block;
            padding: 20px;
            border-radius: 10px;
            margin: 10px;
        }
        .btn {
            padding: 10px 15px;
            font-size: 16px;
            background: #ffcc00;
            color: black;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background: #ff9900;
        }
        .message {
            font-weight: bold;
            font-size: 18px;
            margin: 10px 0;
        }
        .balance {
            font-size: 20px;
            font-weight: bold;
        }
        .back-link {
            display: inline-block;
            margin-top: 10px;
            color: white;
            text-decoration: none;
            background: rgba(0, 0, 0, 0.3);
            padding: 5px 10px;
            border-radius: 5px;
        }
        .back-link:hover {
            background: rgba(0, 0, 0, 0.6);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🃏 Card Draw Game</h2>
        <p class="balance">Your Balance: Rs. <strong><?php echo number_format($balance, 2); ?></strong></p>

        <?php if ($showResult): ?>
            <div>
                <span class="card">You: <?php echo $user_card; ?></span>
                <span class="card">AI: <?php echo $ai_card; ?></span>
            </div>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="number" name="bet_amount" class="input-box" placeholder="Enter bet amount (Min: Rs. 20)" min="20" required>
            <br><br>
            <button type="submit" class="btn">🃏 Draw a Card</button>
        </form>

        <a href="games.php" class="back-link">⬅ Back to Games</a>
    </div>
</body>
</html>
