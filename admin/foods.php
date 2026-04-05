<?php
require_once '../config.php';
require_once '../config/auth.php';

requireAdmin();

$stmt = $conn->query("SELECT profile_image FROM users WHERE id = " . $_SESSION['user_id']);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

$sql = "SELECT * FROM foods WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE ?)";
    $params[] = "%$search%";
}
if ($categoryFilter) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$foods = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Foods</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="../css/admin.css">
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
                <h1>Manage Foods</h1>
                <div class="top-bar-right">
                    <a href="profile.php" class="user-profile">
                        <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="../uploads/profiles/<?= $_SESSION['profile_image'] ?>" alt="Profile" class="profile-img">
                        <?php else: ?>
                        <div class="profile-placeholder"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <span><?= $_SESSION['username'] ?></span>
                    </a>
                    <button class="theme-toggle" onclick="toggleTheme()" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:8px 12px;border-radius:5px;cursor:pointer;font-size:16px;">🌙</button>
                </div>
            </div>

            <div class="page-actions">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                    <select name="category">
                        <option value="">-- Category --</option>
                        <option value="Fast Food" <?= $categoryFilter == 'Fast Food' ? 'selected' : '' ?>>Fast Food</option>
                        <option value="Asian" <?= $categoryFilter == 'Asian' ? 'selected' : '' ?>>Asian</option>
                        <option value="Dessert" <?= $categoryFilter == 'Dessert' ? 'selected' : '' ?>>Dessert</option>
                        <option value="Drink" <?= $categoryFilter == 'Drink' ? 'selected' : '' ?>>Drink</option>
                        <option value="Other" <?= $categoryFilter == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                    <button type="submit" class="btn-primary">Search</button>
                    <a href="foods.php" class="btn-secondary">Reset</a>
                </form>
                <a href="add_food.php" class="btn-primary">+ Add Food</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($foods as $food): ?>
                        <tr>
                            <td><?= $food['id'] ?></td>
                            <td>
                                <?php if ($food['image']): ?>
                                    <img src="../uploads/<?= $food['image'] ?>" class="food-thumb">
                                <?php else: ?>
                                    <span class="no-image">🍽️</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $food['name'] ?></td>
                            <td><?= $food['category'] ?></td>
                            <td>$<?= number_format($food['price'], 2) ?></td>
                            <td>
                                <a href="edit_food.php?id=<?= $food['id'] ?>" class="btn-edit">Edit</a>
                                <a href="delete_food.php?id=<?= $food['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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