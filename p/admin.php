<?php
session_start();
include 'db.php';

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch all users
$usersResult = $conn->query("SELECT id, username, email, role FROM users ORDER BY id ASC");

// Fetch all transactions
$transactionsResult = $conn->query("SELECT user_id, amount, type, status, created_at FROM transactions ORDER BY created_at DESC");

// Fetch pending withdrawals
$withdrawalsResult = $conn->query("SELECT id, user_id, amount, status FROM transactions WHERE type='withdraw' AND status='pending' ORDER BY created_at ASC");

// Fetch pending deposits
$depositsResult = $conn->query("SELECT id, user_id, esewa_number, full_name, amount, transaction_code, payment_proof, status FROM deposits WHERE status='pending' ORDER BY created_at ASC");

// Approve deposit request
if (isset($_POST['approve_deposit'])) {
    $deposit_id = $_POST['deposit_id'];
    $deposit_amount = $_POST['deposit_amount'];
    $user_id = $_POST['user_id'];
    
    // Update deposit status
    $conn->query("UPDATE deposits SET status='approved' WHERE id='$deposit_id'");
    
    // Update user balance
    $conn->query("UPDATE users SET balance = balance + $deposit_amount WHERE id='$user_id'");
    
    header("Location: admin.php");
    exit();
}

// Approve withdrawal request
if (isset($_POST['approve_withdrawal'])) {
    $withdrawal_id = $_POST['withdrawal_id'];
    $user_id = $_POST['user_id'];
    $withdraw_amount = $_POST['withdraw_amount'];

    // Fetch user balance
    $userBalanceQuery = $conn->query("SELECT balance FROM users WHERE id='$user_id'");
    $userBalance = $userBalanceQuery->fetch_assoc()['balance'];

    if ($userBalance >= $withdraw_amount) {
        // Deduct amount from user balance
        $conn->query("UPDATE users SET balance = balance - $withdraw_amount WHERE id='$user_id'");

        // Update transaction status
        $conn->query("UPDATE transactions SET status='approved' WHERE id='$withdrawal_id'");

        header("Location: admin.php");
        exit();
    } else {
        echo "<script>alert('Insufficient balance to approve this withdrawal!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - N£P 11</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: auto; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #333; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        a { text-decoration: none; color: red; font-weight: bold; }
        a:hover { text-decoration: underline; }
        .back-link { display: block; margin-top: 20px; font-size: 16px; }
        .approve-btn { background-color: green; color: white; padding: 5px 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Panel</h2>
        
        <!-- Users Table -->
        <h3>Manage Users</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
            <?php while ($user = $usersResult->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                <td><a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a></td>
            </tr>
            <?php } ?>
        </table>

        <!-- Pending Deposits Table -->
        <h3>Pending Deposits</h3>
        <table>
            <tr>
                <th>Deposit ID</th>
                <th>User ID</th>
                <th>Esewa Number</th>
                <th>Full Name</th>
                <th>Amount</th>
                <th>Transaction Code</th>
                <th>Payment Proof</th>
                <th>Action</th>
            </tr>
            <?php while ($deposit = $depositsResult->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($deposit['id']); ?></td>
                <td><?php echo htmlspecialchars($deposit['user_id']); ?></td>
                <td><?php echo htmlspecialchars($deposit['esewa_number']); ?></td>
                <td><?php echo htmlspecialchars($deposit['full_name']); ?></td>
                <td><?php echo number_format($deposit['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($deposit['transaction_code']); ?></td>
                <td><a href="uploads/<?php echo htmlspecialchars($deposit['payment_proof']); ?>" target="_blank">View</a></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="deposit_id" value="<?php echo $deposit['id']; ?>">
                        <input type="hidden" name="deposit_amount" value="<?php echo $deposit['amount']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $deposit['user_id']; ?>">
                        <button type="submit" name="approve_deposit" class="approve-btn">Approve</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>

        <!-- Pending Withdrawals Table -->
        <h3>Pending Withdrawals</h3>
        <table>
            <tr>
                <th>Withdrawal ID</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($withdrawal = $withdrawalsResult->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($withdrawal['id']); ?></td>
                <td><?php echo htmlspecialchars($withdrawal['user_id']); ?></td>
                <td><?php echo number_format($withdrawal['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($withdrawal['status']); ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $withdrawal['user_id']; ?>">
                        <input type="hidden" name="withdraw_amount" value="<?php echo $withdrawal['amount']; ?>">
                        <button type="submit" name="approve_withdrawal" class="approve-btn">Approve</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>

        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
