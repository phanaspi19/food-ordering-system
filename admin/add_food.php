<?php
require_once '../config.php';
require_once '../config/auth.php';

requireAdmin();

$stmt = $conn->query("SELECT profile_image FROM users WHERE id = " . $_SESSION['user_id']);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category = $_POST['category'];
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $image = time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $image);
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO foods (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $description, $price, $category, $image])) {
        header("Location: foods.php?success=1");
        exit();
    } else {
        $error = "Could not save!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Food</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Nunito', sans-serif; }
        body { background: linear-gradient(120deg, #f6d365 0%, #fda085 100%); min-height: 100vh; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 30px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px 25px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .top-bar h1 { color: #ff6b6b; font-size: 28px; margin: 0; }
        .top-bar span { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); padding: 10px 20px; border-radius: 20px; font-weight: 600; color: white; }
        .form-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.15); max-width: 600px; margin: 0 auto; }
        .form-card h2 { text-align: center; color: #ff6b6b; font-size: 26px; margin-bottom: 30px; }
        .form-card .form-group { margin-bottom: 20px; }
        .form-card label { display: block; margin-bottom: 8px; color: #555; font-weight: 700; font-size: 15px; }
        .form-card input, .form-card textarea, .form-card select { width: 100%; padding: 14px 18px; border: 2px solid #eee; border-radius: 12px; font-size: 15px; transition: all 0.3s; }
        .form-card input:focus, .form-card textarea:focus, .form-card select:focus { outline: none; border-color: #ff6b6b; box-shadow: 0 0 15px rgba(255,107,107,0.2); }
        .form-card textarea { resize: vertical; min-height: 100px; }
        .file-upload { position: relative; display: inline-block; width: 100%; }
        .file-upload-label { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 20px; border: 2px dashed #ff6b6b; border-radius: 12px; cursor: pointer; color: #ff6b6b; font-weight: 600; transition: all 0.3s; }
        .file-upload-label:hover { background: #fff5f5; }
        .file-upload input { display: none; }
        .form-actions { display: flex; gap: 15px; justify-content: center; margin-top: 30px; }
        .btn-save { background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%); color: white; padding: 14px 35px; border: none; border-radius: 25px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-save:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(255,107,107,0.4); }
        .btn-back { background: #eee; color: #555; padding: 14px 35px; border: none; border-radius: 25px; font-size: 16px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; }
        .btn-back:hover { background: #ddd; transform: translateY(-3px); }
        .error-msg { background: linear-gradient(135deg, #ffe0e0, #ffcccc); color: #e74c3c; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; text-align: center; border: 2px solid rgba(255,150,150,0.3); }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="foods.php" class="active">Foods</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="expenses.php">Expenses</a></li>
                <li><a href="money_tracking.php">Money Tracking</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>+ Add Food</h1>
                <div class="top-bar-right">
                    <a href="profile.php" class="user-profile">
                        <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="../uploads/profiles/<?= $_SESSION['profile_image'] ?>" alt="Profile" class="profile-img">
                        <?php else: ?>
                        <div class="profile-placeholder"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <span><?= $_SESSION['username'] ?></span>
                    </a>
                    <button class="theme-toggle" onclick="toggleTheme()" style="background: #ff6b6b; border: none; color: white; padding: 8px 15px; border-radius: 20px; cursor: pointer; font-size: 16px;">🌙</button>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?= $error ?></div>
            <?php endif; ?>

            <div class="form-card">
                <h2>Add New Food</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Food Name</label>
                        <input type="text" name="name" placeholder="Enter name..." required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="Enter description..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" name="price" step="0.01" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="Fast Food">Fast Food</option>
                            <option value="Asian">Asian</option>
                            <option value="Dessert">Dessert</option>
                            <option value="Drink">Drink</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Image</label>
                        <div class="file-upload">
                            <label for="image" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Select Image
                            </label>
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <a href="foods.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="../js/toast.js"></script>
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