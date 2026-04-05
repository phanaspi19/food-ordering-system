<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: customer/foods.php");
            }
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Food Ordering</title>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #ff4757;
            --secondary-color: #2f3542;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --gradient: linear-gradient(135deg, #ff4757 0%, #ff6b81 100%);
        }

        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Poppins', 'Kantumruy Pro', sans-serif;
        }

        body {
            background: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        .login-container {
            background: var(--glass-bg);
            width: 900px;
            max-width: 95%;
            height: 550px;
            display: flex;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .login-image-section {
            flex: 1;
            position: relative;
            background: url('img/food.jpg') center/cover;
            display: none;
        }

        @media (min-width: 768px) {
            .login-image-section { display: block; }
        }

        .image-overlay {
            position: absolute;
            bottom: 40px; left: 40px; color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .login-form-section {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header h2 {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 5px;
            font-weight: 700;
        }

        .form-header p {
            color: #747d8c;
            margin-bottom: 30px;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a4b0be;
            transition: 0.3s;
        }

        .input-wrapper input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #f1f2f6;
            border-radius: 12px;
            outline: none;
            transition: 0.3s;
            font-size: 1rem;
        }

        .input-wrapper input:focus {
            border-color: var(--primary-color);
        }

        .input-wrapper input:focus + i {
            color: var(--primary-color);
        }

        .error-message {
            background: #ffeef0;
            color: #ff4757;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border-left: 5px solid #ff4757;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3);
            transition: 0.3s;
            margin-bottom: 15px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 71, 87, 0.4);
        }

        .btn-register {
            display: block;
            text-align: center;
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 500;
            padding: 10px;
            border: 2px solid #f1f2f6;
            border-radius: 12px;
            transition: 0.3s;
        }

        .btn-register:hover {
            background: #f1f2f6;
        }

        .form-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 0.9rem;
        }

        .form-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-image-section">
            <div class="image-overlay">
                <h2>Original Taste</h2>
                <p>Fast order, delivered to your door</p>
            </div>
        </div>

        <div class="login-form-section">
            <div class="form-header">
                <h2>Login</h2>
                <p>Please enter your details below</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message" style="color: red;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div style="text-align: right; margin-bottom: 20px;">
                    <a href="#" style="color: #747d8c; text-decoration: none; font-size: 0.85rem;">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">Login</button>
                <a href="register.php" class="btn-register">Create New Account</a>
            </form>

            <div class="form-footer">
                <p>Need help? <a href="#">Contact Support</a></p>
            </div>
        </div>
    </div>

</body>
</html>