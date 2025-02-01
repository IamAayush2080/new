<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $esewa_number = $_POST['esewa_number'];
    $full_name = $_POST['full_name'];
    $amount = $_POST['amount'];
    $transaction_code = $_POST['transaction_code'];
    $target_dir = "uploads/";
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $payment_proof = $target_dir . basename($_FILES["payment_proof"]["name"]);
    move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $payment_proof);
    
    $stmt = $conn->prepare("INSERT INTO deposits (user_id, esewa_number, full_name, amount, transaction_code, payment_proof) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdss", $user_id, $esewa_number, $full_name, $amount, $transaction_code, $payment_proof);
    $stmt->execute();
    
    echo "<script>alert('Deposit request submitted successfully!'); window.location.href='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Money - N£P 11</title>
    <style>
        body {
            background: url('assets/bg.jpg') no-repeat center center/cover;
            font-family: Arial, sans-serif;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .container {
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            max-width: 500px;
            width: 100%;
        }
        img {
            width: 150px;
        }
        h2 {
            color: #00ff00;
        }
        form input, form button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }
        form input {
            background: #ddd;
        }
        form button {
            background: #00ff00;
            color: black;
            cursor: pointer;
            font-weight: bold;
        }
        .motivation {
            margin-top: 20px;
            font-size: 16px;
            font-style: italic;
            color: #ffcc00;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="assets/esewa.png" alt="eSewa Logo">
        <h2>Deposit Money</h2>
        <p><strong>Send Money To:</strong><br>
           <strong>eSewa Number:</strong> 98XXXXXXXX <br>
           <strong>Name:</strong> Admin N£P 11</p>
        
        <form action="deposit.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="esewa_number" placeholder="Your eSewa Number" required>
            <input type="text" name="full_name" placeholder="Your Full Name" required>
            <input type="number" name="amount" min="400" placeholder="Amount (Min 400)" required>
            <input type="text" name="transaction_code" placeholder="Transaction Code" required>
            <input type="file" name="payment_proof" accept="image/*,application/pdf" required>
            <button type="submit">Submit Deposit</button>
        </form>
        <p class="motivation">"Success is not final, failure is not fatal: It is the courage to continue that counts."</p>
    </div>
</body>
</html>
