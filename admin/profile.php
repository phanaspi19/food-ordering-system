<?php
require_once '../config.php';
require_once '../config/auth.php';

requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $upload_dir = '../uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'admin_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
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
    <title>Admin Profile</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" style="text-decoration: none; color: white;">
                    <h2>Admin Panel</h2>
                </a>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="foods.php">Foods</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="expenses.php">Expenses</a></li>
                <li><a href="money_tracking.php">Money Tracking</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>My Profile</h1>
                <div class="top-bar-right">
                    <div class="user-profile">
                        <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="../uploads/profiles/<?= $_SESSION['profile_image'] ?>" alt="Profile" class="profile-img">
                        <?php else: ?>
                        <div class="profile-placeholder"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <span><?= $_SESSION['username'] ?></span>
                    </div>
                    <button class="theme-toggle" onclick="toggleTheme()" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:8px 12px;border-radius:5px;cursor:pointer;font-size:16px;">🌙</button>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="success-message"><?= $message ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
            <?php endif; ?>

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
                        <p><strong>Role:</strong> <?= $user['role'] ?></p>
                        <p><strong>Created:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
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
        </main>
    </div>
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

<style>
.profile-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.profile-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.profile-card h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
}

.profile-image-section {
    text-align: center;
    margin-bottom: 20px;
}

.profile-large, .profile-placeholder-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto;
}

.profile-placeholder-large {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    font-weight: bold;
}

.profile-upload-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.profile-upload-form input[type="file"] {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.profile-info p {
    margin: 10px 0;
    color: #333;
}

.password-form .form-group {
    margin-bottom: 15px;
}

.password-form label {
    display: block;
    margin-bottom: 5px;
    color: #666;
    font-size: 14px;
}

.password-form input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.error-message {
    background: #ffeef0;
    color: #ff4757;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border-left: 4px solid #ff4757;
}
</style>