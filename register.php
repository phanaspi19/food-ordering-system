<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        $error = "Username already taken!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'customer')");
        if ($stmt->execute([$username, $password])) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $error = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Food Ordering</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/toast.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-page">
        <div class="login-card-split">
            <div class="login-image-section">
                <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600" alt="Food">
                <div class="image-overlay">
                    <h2>Join Us</h2>
                    <p>Create your account now</p>
                </div>
            </div>
            <div class="login-form-section">
                <div class="form-header">
                    <h2>Register</h2>
                    <p>Please fill in your details below</p>
                </div>
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                <form method="POST" class="login-form">
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn-login">Register</button>
                    <a href="login.php" class="btn-register">Login</a>
                </form>
                <div class="form-footer">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($_GET['registered'])): ?>
    <script src="js/toast.js"></script>
    <script>showSuccess('Registration successful! Please login.')</script>
    <?php endif; ?>
</body>
</html>