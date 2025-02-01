<?php
session_start();
include 'db.php';

$query = "SELECT username, total_winnings FROM users ORDER BY total_winnings DESC LIMIT 10";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - N£P 11</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: #1e1e2e;
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        th {
            background: rgba(0, 0, 0, 0.3);
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
        <h2>🏆 Leaderboard</h2>
        <table>
            <tr>
                <th>Player</th>
                <th>Total Winnings (Rs.)</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo number_format($row['total_winnings'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <a href="dashboard.php" class="back-link">⬅ Back to Dashboard</a>
    </div>
</body>
</html>




