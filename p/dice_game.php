<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user balance
$result = $conn->query("SELECT balance FROM users WHERE id='$user_id'");
$user = $result->fetch_assoc();
$balance = $user['balance'];

// Track game plays for "Lucky Start"
if (!isset($_SESSION['game_count'])) {
    $_SESSION['game_count'] = 0;
}

$showResult = false;
$user_roll = 0;
$ai_roll = 0;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bet_amount = (int) $_POST['bet_amount'];

    if ($bet_amount <= 0 || $bet_amount > $balance) {
        $message = "❌ Invalid bet amount!";
    } else {
        // Deduct bet amount initially
        $conn->query("UPDATE users SET balance = balance - $bet_amount WHERE id='$user_id'");

        // Dice roll logic
        $_SESSION['game_count']++;
        if ($_SESSION['game_count'] <= 3 && $bet_amount <= 50) {
            // Give user a better chance in first 2-3 plays
            $user_roll = rand(4, 6);
            $ai_roll = rand(1, 5);
        } else {
            // Normal odds (favoring AI)
            $user_roll = rand(1, 6);
            $ai_roll = rand(3, 6);
        }

        if ($user_roll > $ai_roll) {
            $winnings = $bet_amount * 2;
            $conn->query("UPDATE users SET balance = balance + $winnings WHERE id='$user_id'");
            $message = "🎉 You rolled a $user_roll, AI rolled a $ai_roll. You won Rs. $winnings!";
        } elseif ($user_roll == $ai_roll) {
            $conn->query("UPDATE users SET balance = balance + $bet_amount WHERE id='$user_id'");
            $message = "😐 It's a draw! Both rolled $user_roll. Bet refunded.";
        } else {
            $message = "❌ You rolled a $user_roll, AI rolled a $ai_roll. You lost Rs. $bet_amount.";
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
    <title>Dice Game - N£P 11</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            text-align: center;
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            color: white;
        }
        .container {
            max-width: 400px;
            margin: auto;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }
        .message {
            font-weight: bold;
            margin: 10px 0;
            font-size: 18px;
        }
        .input-box {
            padding: 10px;
            margin: 10px 0;
            width: 80%;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            text-align: center;
        }
        .btn {
            padding: 10px 15px;
            font-size: 16px;
            background: #ffcc00;
            color: black;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }
        .btn:hover {
            background: #ff9900;
        }
        .dice-container {
            display: flex;
            justify-content: space-evenly;
            margin: 20px 0;
        }
        .dice {
            width: 60px;
            height: 60px;
            background: white;
            color: black;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            border-radius: 10px;
            animation: roll 1s ease-in-out;
        }
        @keyframes roll {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(90deg); }
            50% { transform: rotate(180deg); }
            75% { transform: rotate(270deg); }
            100% { transform: rotate(360deg); }
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
        <h2>🎲 Dice Game</h2>
        <p class="balance">Your Balance: Rs. <strong><?php echo number_format($balance, 2); ?></strong></p>

        <?php if ($showResult): ?>
            <div class="dice-container">
                <div class="dice"><?php echo $user_roll; ?></div>
                <div class="dice"><?php echo $ai_roll; ?></div>
            </div>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="number" name="bet_amount" class="input-box" placeholder="Enter bet amount" min="1" required>
            <button type="submit" class="btn">🎲 Roll the Dice</button>
        </form>

        <a href="games.php" class="back-link">⬅ Back to Games</a>
    </div>
</body>
</html>
