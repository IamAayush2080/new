<?php
session_start();
include 'db.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        // Registration Logic
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        
        // Check if password is set in the form
        if (isset($_POST['password']) && !empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        } else {
            $error = "Password is required!";
        }

        // Set the default role as 'user'
        $role = 'user';

        // Check if email already exists
        $checkUser = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($checkUser->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert user data into the database
            $conn->query("INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')");
            $success = "Registration successful! You can log in now.";
        }
    } elseif (isset($_POST['login'])) {
        // Login Logic
        $email = $conn->real_escape_string($_POST['email']);
        
        // Check if password is set and not empty
        if (isset($_POST['password']) && !empty($_POST['password'])) {
            $password = $_POST['password'];
        } else {
            $error = "Password is required!";
        }

        // Query to fetch user details including role
        $result = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Store user data in session including role
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];  // Set the role in the session

                // Redirect based on role
                if ($_SESSION['role'] == 'admin') {
                    header("Location: admin.php"); // Redirect to admin panel if admin
                } else {
                    header("Location: dashboard.php"); // Redirect to user dashboard
                }
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No account found with this email!";
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
    <div class="container">
        <h2>N£P 11 Login & Registration</h2>
        
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
    </div>
</body>
</html>