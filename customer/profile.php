<?php
require_once '../config.php';
require_once '../config/auth.php';

requireLogin();

if ($_SESSION['role'] !== 'customer') {
    header("Location: ../admin/dashboard.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $upload_dir = '../uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'customer_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $new_filename;
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_ext), $allowed_ext)) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->execute([$new_filename, $_SESSION['user_id']]);
                $_SESSION['profile_image'] = $new_filename;
                $message = "Profile image updated successfully!";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        $message = "Password changed successfully!";
    }
}

$stmt = $conn->query("SELECT profile_image, username, role, created_at FROM users WHERE id = " . $_SESSION['user_id']);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Online Food Ordering</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="../css/customer.css">
    <style>
body.dark-mode { background-color: #1a1a2e !important; }
body.dark-mode .navbar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important; }
body.dark-mode .container { background-color: #1a1a2e !important; }
body.dark-mode h1 { color: #fff !important; }
body.dark-mode .profile-card { background-color: #242442 !important; color: #fff !important; }
body.dark-mode .profile-card h3 { color: #fff !important; border-color: #4a4a70 !important; }
body.dark-mode .profile-info p { color: #d0d0d0 !important; border-color: #3a3a5a !important; }
body.dark-mode .profile-info strong { color: #fff !important; }
body.dark-mode .profile-upload-form input[type="file"] { background: #1a1a2e !important; border-color: #4a4a70 !important; color: #fff !important; }
body.dark-mode .password-form label { color: #d0d0d0 !important; }
body.dark-mode .password-form input { background: #1a1a2e !important; border-color: #4a4a70 !important; color: #fff !important; }
body.dark-mode .success-message { background: #1a3a2a !important; color: #7dd87d !important; }
body.dark-mode .error-message { background: #3a1a1a !important; color: #d87d7d !important; }
</style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Food Order</div>
        <div class="nav-links">
            <a href="foods.php">Foods</a>
            <a href="cart.php">Cart</a>
            <a href="my_orders.php">Orders</a>
            <a href="profile.php" class="active">Profile</a>
            <div class="user-profile">
                <?php if (!empty($_SESSION['profile_image'])): ?>
                <img src="../uploads/profiles/<?= $_SESSION['profile_image'] ?>" class="profile-img-small">
                <?php else: ?>
                <span class="profile-initial"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></span>
                <?php endif; ?>
                <span><?= $_SESSION['username'] ?></span>
            </div>
            <button class="theme-toggle" onclick="toggleTheme()" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:8px 12px;border-radius:5px;cursor:pointer;font-size:16px;">🌙</button>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
        <div class="success-message"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <h1>My Profile</h1>

        <div class="profile-container">
            <div class="profile-card">
                <h3>Profile Picture</h3>
                <div class="profile-image-section">
                    <?php if (!empty($_SESSION['profile_image'])): ?>
                    <img src="../uploads/profiles/<?= $_SESSION['profile_image'] ?>" alt="Profile" class="profile-large">
                    <?php else: ?>
                    <div class="profile-placeholder-large"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <form method="POST" enctype="multipart/form-data" class="profile-upload-form">
                    <input type="file" name="profile_image" accept="image/*" required>
                    <button type="submit" name="update_profile" class="btn-update">Change Photo</button>
                </form>
            </div>

            <div class="profile-card">
                <h3>Account Info</h3>
                <div class="profile-info">
                    <p><strong>Name:</strong> <?= $user['username'] ?></p>
                    <p><strong>Role:</strong> Customer</p>
                    <p><strong>Joined:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>

            <div class="profile-card">
                <h3>Change Password</h3>
                <form method="POST" class="password-form">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn-update">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <style>
    .profile-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 25px;
    }

    .profile-card {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .profile-card h3 {
        margin: 0 0 25px 0;
        font-size: 20px;
        color: #2c3e50;
        font-weight: 700;
        border-bottom: 2px solid #667eea;
        padding-bottom: 12px;
    }

    .profile-image-section {
        text-align: center;
        margin-bottom: 25px;
    }

    .profile-large, .profile-placeholder-large {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto;
        border: 4px solid #667eea;
        box-shadow: 0 8px 25px rgba(102,126,234,0.3);
    }

    .profile-placeholder-large {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        font-weight: bold;
    }

    .profile-upload-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .profile-upload-form input[type="file"] {
        padding: 12px;
        border: 2px dashed #e0e0e0;
        border-radius: 12px;
        background: #f8f9fa;
        cursor: pointer;
    }

    .profile-upload-form input[type="file"]:hover {
        border-color: #667eea;
    }

    .profile-info p {
        margin: 18px 0;
        color: #555;
        font-size: 15px;
        display: flex;
        justify-content: space-between;
        padding-bottom: 12px;
        border-bottom: 1px solid #eee;
    }

    .profile-info p:last-child {
        border-bottom: none;
    }

    .profile-info strong {
        color: #2c3e50;
    }

    .password-form .form-group {
        margin-bottom: 20px;
    }

    .password-form label {
        display: block;
        margin-bottom: 8px;
        color: #666;
        font-size: 14px;
        font-weight: 600;
    }

    .password-form input {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .password-form input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 15px rgba(102,126,234,0.2);
    }

    .error-message {
        background: linear-gradient(135deg, #ffeef0 0%, #ffdde4 100%);
        color: #e74c3c;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        border-left: 4px solid #e74c3c;
    }

    .success-message {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
    }

    .btn-update {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 14px 28px;
        border: none;
        border-radius: 25px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-update:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102,126,234,0.4);
    }
    </style>
<script>
function toggleTheme() {
    const body = document.body;
    const btn = document.querySelector('.theme-toggle');
    const isDark = body.classList.contains('dark-mode');
    body.classList.toggle('dark-mode');
    btn.textContent = isDark ? '🌙' : '☀️';
    localStorage.setItem('darkMode', isDark ? 'disabled' : 'enabled');
}
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    document.querySelector('.theme-toggle').textContent = '☀️';
}
</script>
</body>
</html>