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

if (!isset($_SESSION['roulette_count'])) {
    $_SESSION['roulette_count'] = 0;
    $_SESSION['forced_win'] = false;
}

$showResult = false;
$user_number = "";
$spin_result = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bet_amount = (int) $_POST['bet_amount'];
    $user_number = (int) $_POST['bet_number'];

    if ($bet_amount < 20) {
        $message = "❌ Minimum bet amount is Rs. 20!";
    } elseif ($bet_amount > $balance) {
        $message = "❌ You don't have enough balance!";
    } elseif ($user_number < 1 || $user_number > 36) {
        $message = "❌ Please choose a number between 1 and 36!";
    } else {
        $conn->query("UPDATE users SET balance = balance - $bet_amount WHERE id='$user_id'");

        $_SESSION['roulette_count']++;

        // Spin logic
        $spin_result = rand(1, 36);

        // Ensure user wins at least once in first 3 rounds
        if ($_SESSION['roulette_count'] <= 3 && !$_SESSION['forced_win']) {
            if ($_SESSION['roulette_count'] == 3) {
                $spin_result = $user_number;
                $_SESSION['forced_win'] = true;
            } elseif (rand(1, 100) <= 40) {
                $spin_result = $user_number;
            }
        }

        if ($spin_result == $user_number) {
            $winnings = $bet_amount * 10;
            $conn->query("UPDATE users SET balance = balance + $winnings WHERE id='$user_id'");
            $message = "🎉 The wheel landed on $spin_result! You won Rs. $winnings!";
        } else {
            $message = "❌ The wheel landed on $spin_result. You lost Rs. $bet_amount.";
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
    <title>Roulette Game - N£P 11</title>
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
        .wheel-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 20px auto;
        }
        .wheel {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(red 0% 10%, black 10% 20%, red 20% 30%, black 30% 40%, red 40% 50%, black 50% 60%, red 60% 70%, black 70% 80%, red 80% 90%, black 90% 100%);
            position: absolute;
            transition: transform 3s ease-out;
        }
        .pointer {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 20px;
            background: white;
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
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
        <h2>🎡 Roulette Spin</h2>
        <p class="balance">Your Balance: Rs. <strong><?php echo number_format($balance, 2); ?></strong></p>

        <div class="wheel-container">
            <div class="pointer"></div>
            <div class="wheel" id="roulette-wheel"></div>
        </div>

        <?php if ($showResult): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="number" name="bet_amount" class="input-box" placeholder="Enter bet amount (Min: Rs. 20)" min="20" required>
            <br><br>
            <input type="number" name="bet_number" class="input-box" placeholder="Choose a number (1-36)" min="1" max="36" required>
            <br><br>
            <button type="submit" class="btn" onclick="spinWheel()">🎡 Spin the Wheel</button>
        </form>

        <a href="games.php" class="back-link">⬅ Back to Games</a>
    </div>

    <script>
        function spinWheel() {
            let wheel = document.getElementById("roulette-wheel");
            let spinAngle = Math.floor(Math.random() * 3600) + 1800;
            wheel.style.transform = "rotate(" + spinAngle + "deg)";
        }
    </script>
</body>
</html>
