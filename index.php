<?php
session_start();
include 'db.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        // Validate Input
        if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['password'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format!";
            } else {
                // Check if email exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $error = "Email already exists!";
                } else {
                    // Hash password and insert user
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $role = "user"; // Default role

                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
                    if ($stmt->execute()) {
                        $success = "Registration successful! You can log in now.";
                    } else {
                        $error = "Error in registration!";
                    }
                }
                $stmt->close();
            }
        } else {
            $error = "All fields are required!";
        }
    } elseif (isset($_POST['login'])) {
        // Validate Input
        if (!empty($_POST['email']) && !empty($_POST['password'])) {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            // Check user in database
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($userId, $username, $hashedPassword, $role);
                $stmt->fetch();

                // Verify password
                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['user_id'] = $userId;  // Fixed variable usage
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;  

                    // Redirect based on role
                    if ($role == 'admin') {
                        header("Location: admin.php"); 
                    } else {
                        header("Location: dashboard.php"); 
                    }
                    exit();
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "No account found with this email!";
            }
            $stmt->close();
        } else {
            $error = "Email and password are required!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N£P 11 - Login & Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="wrapper">
        <header>
            <h1 class="logo">N£P 11</h1>
        </header>
        
        <div class="container">
            <h2>Login & Registration</h2>
            
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

            <div class="form-container">
                <form method="POST">
                    <h3>Register</h3>
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="register">Register</button>
                </form>

                <form method="POST">
                    <h3>Login</h3>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="login">Login</button>
                </form>
            </div>
            
            <footer>
                <p>"Success starts with a single step, take yours today!"</p>
            </footer>
        </div>
    </div>
</body>
</html>
