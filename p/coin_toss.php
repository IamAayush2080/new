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

if (!isset($_SESSION['toss_count'])) {
    $_SESSION['toss_count'] = 0;  // Track number of tosses
    $_SESSION['forced_win'] = false;  // Ensure one win in first 3 tries
}

$showResult = false;
$user_choice = "";
$coin_result = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bet_amount = (int) $_POST['bet_amount'];
    $user_choice = $_POST['choice']; // 'heads' or 'tails'

    if ($bet_amount < 20) {
        $message = "❌ Minimum bet amount is Rs. 20!";
    } elseif ($bet_amount > $balance) {
        $message = "❌ You don't have enough balance!";
    } else {
        // Deduct bet amount initially
        $conn->query("UPDATE users SET balance = balance - $bet_amount WHERE id='$user_id'");

        $_SESSION['toss_count']++;

        // First 3 games logic (at most 1 win)
        if ($_SESSION['toss_count'] <= 3) {
            if (!$_SESSION['forced_win'] && $_SESSION['toss_count'] == 3) {
                // On the 3rd toss, force a win if no previous win
                $coin_result = $user_choice;
                $_SESSION['forced_win'] = true;
            } else {
                // Random but more likely to lose
                $coin_result = (rand(1, 100) <= 70) ? (($user_choice == "heads") ? "tails" : "heads") : $user_choice;
            }
        } else {
            // Normal fair toss (50-50)
            $coin_result = (rand(0, 1) == 0) ? "heads" : "tails";
        }

        if ($user_choice == $coin_result) {
            $winnings = $bet_amount * 2;
            $conn->query("UPDATE users SET balance = balance + $winnings WHERE id='$user_id'");
            $message = "🎉 You chose $user_choice, coin landed on $coin_result. You won Rs. $winnings!";
        } else {
            $message = "❌ You chose $user_choice, but coin landed on $coin_result. You lost Rs. $bet_amount.";
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
    <title>Coin Toss Game - N£P 11</title>
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
        .coin {
            width: 80px;
            height: 80px;
            background: white;
            color: black;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            border-radius: 50%;
            animation: flip 1s ease-in-out;
        }
        @keyframes flip {
            0% { transform: rotateY(0deg); }
            50% { transform: rotateY(180deg); }
            100% { transform: rotateY(360deg); }
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
        <h2>🪙 Coin Toss Game</h2>
        <p class="balance">Your Balance: Rs. <strong><?php echo number_format($balance, 2); ?></strong></p>

        <?php if ($showResult): ?>
            <div class="coin"><?php echo ucfirst($coin_result); ?></div>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="number" name="bet_amount" class="input-box" placeholder="Enter bet amount (Min: Rs. 20)" min="20" required>
            <br>
            <label>
                <input type="radio" name="choice" value="heads" required> Heads
            </label>
            <label>
                <input type="radio" name="choice" value="tails" required> Tails
            </label>
            <br><br>
            <button type="submit" class="btn">🪙 Flip the Coin</button>
        </form>

        <a href="games.php" class="back-link">⬅ Back to Games</a>
    </div>
</body>
</html>
