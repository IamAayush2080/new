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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games - N£P 11</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; text-align: center; }
        .container { max-width: 600px; margin: auto; }
        .game-list { list-style-type: none; padding: 0; }
        .game-list li { background: #ddd; margin: 10px; padding: 15px; border-radius: 5px; }
        .game-list a { text-decoration: none; font-weight: bold; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Available Games</h2>
        <p>Your Balance: <strong>Rs. <?php echo number_format($balance, 2); ?></strong></p>

        <ul class="game-list">
            <li><a href="dice_game.php">🎲 Dice Game</a></li>
            <li><a href="coin_toss.php">🪙 Toss the Coin</a></li>
            <li><a href="card_draw.php">🃏 Card Draw Game</a></li>
            <li><a href="roulette.php">🎡  Roulette Game</a></li>
            <li><a href="slot_game.php">🎡  Slot Game Win 3x</a></li>

        </ul>

        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
