<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $balance);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N£P 11 - Dashboard</title>
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-image: url('assets/background.jpg'); /* Background image */
            background-size: cover;
            background-position: center;
            color: #fff;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        a {
            text-decoration: none;
            color: #fff;
        }

        /* Header Styles */
        header {
            background-color: rgba(0, 123, 255, 0.8);
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid #fff;
        }

        .logo {
            font-size: 36px;
            font-weight: bold;
        }

        /* Dashboard Container */
        .dashboard-container {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            height: 100%;
        }

        /* Dashboard Content */
        .dashboard-content {
            background: rgba(0, 0, 0, 0.7); /* Semi-transparent background */
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
            margin-top: 20px;
            text-align: center;
            width: 80%;
            max-width: 900px;
        }

        .dashboard-content h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }

        .dashboard-content p {
            font-size: 22px;
            margin-bottom: 30px;
        }

        .dashboard-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .dashboard-options .btn {
            display: inline-block;
            background-color: #28a745;
            padding: 15px 30px;
            color: white;
            text-align: center;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .dashboard-options .btn:hover {
            background-color: #218838;
        }

        .dashboard-options .logout-btn {
            background-color: #dc3545;
        }

        .dashboard-options .logout-btn:hover {
            background-color: #c82333;
        }

        /* Footer Styles */
        footer {
            background-color: rgba(0, 123, 255, 0.8);
            color: #fff;
            text-align: center;
            padding: 15px;
            width: 100%;
            position: fixed;
            bottom: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1 class="logo">N£P 11</h1>
        </header>

        <div class="dashboard-content">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Your Balance: <strong>Rs. <?php echo number_format($balance, 2); ?></strong></p>

            <div class="dashboard-options">
                <a href="deposit.php" class="btn">Deposit Money</a>
                <a href="withdraw.php" class="btn">Withdraw Money</a>
                <a href="transactions.php" class="btn">Transaction History</a>
                <a href="games.php" class="btn">Play Games</a>
                <a href="leaderboard.php" class="btn">LEADERBOARD</a>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
        </div>

        <footer>
            <p>&copy; 2025 N£P 11 - All Rights Reserved</p>
        </footer>
    </div>
</body>
</html>
