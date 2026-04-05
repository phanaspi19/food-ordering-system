<?php
require_once '../config.php';
require_once '../config/auth.php';

requireLogin();

$stmt = $conn->query("SELECT profile_image FROM users WHERE id = " . $_SESSION['user_id']);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT * FROM foods WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$foods = $stmt->fetchAll();

$stmt = $conn->query("SELECT DISTINCT category FROM foods");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foods - Online Food Ordering</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="../css/customer.css">
</head>
<style>
body.dark-mode { background-color: #1a1a2e !important; }
body.dark-mode .navbar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important; }
body.dark-mode .container { background-color: #1a1a2e !important; }
body.dark-mode .food-card { background-color: #242442 !important; color: #fff !important; }
body.dark-mode h1 { color: #fff !important; }
body.dark-mode .food-name { color: #fff !important; }
body.dark-mode .food-desc { color: #a0a0b0 !important; }
body.dark-mode .search-filter-bar { background-color: #242442 !important; }
body.dark-mode .search-filter-bar input, body.dark-mode .search-filter-bar select { background-color: #32325a !important; color: #fff !important; border-color: #4a4a70 !important; }
</style>
<body>
    <nav class="navbar">
        <div class="nav-brand">Food Order</div>
        <div class="nav-links">
            <a href="foods.php">Foods</a>
            <a href="cart.php">Cart</a>
            <a href="my_orders.php">Orders</a>
            <a href="profile.php" class="user-profile">
                <?php if (!empty($_SESSION['profile_image'])): ?>
                <img src="../uploads/profiles/<?= $_SESSION['profile_image'] ?>" class="profile-img-small">
                <?php else: ?>
                <span class="profile-initial"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></span>
                <?php endif; ?>
                <span><?= $_SESSION['username'] ?></span>
            </a>
            <button class="theme-toggle" onclick="toggleTheme()" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:8px 12px;border-radius:5px;cursor:pointer;font-size:16px;">🌙</button>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <?php if (isset($message)): ?>
    <div class="success-message"><?= $message ?></div>
    <?php endif; ?>

    <div class="container">
        <div class="search-filter-bar">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search foods..." value="<?= $search ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category'] ?>" <?= $category == $cat['category'] ? 'selected' : '' ?>><?= $cat['category'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary">Search</button>
                <?php if ($search || $category): ?>
                    <a href="foods.php" class="btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <h1>All Foods</h1>
        
        <?php if (empty($foods)): ?>
            <p class="empty-cart">No foods found!</p>
        <?php else: ?>
            <div class="food-grid">
                <?php foreach ($foods as $food): ?>
                <div class="food-card">
                    <?php if ($food['image']): ?>
                        <img src="../uploads/<?= $food['image'] ?>" class="food-image">
                    <?php else: ?>
                        <div class="food-image-placeholder">🍽️</div>
                    <?php endif; ?>
                    <div class="food-category"><?= $food['category'] ?></div>
                    <div class="food-name"><?= $food['name'] ?></div>
                    <div class="food-desc"><?= $food['description'] ?></div>
                    <div class="food-price">$<?= number_format($food['price'], 2) ?></div>
                    <form method="POST" action="add_to_cart.php">
                        <input type="hidden" name="food_id" value="<?= $food['id'] ?>">
                        <button type="submit" class="btn-add">Add to Cart</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['added'])): ?>
    <script src="../js/toast.js"></script>
    <script>showSuccess('Added to cart!')</script>
    <?php endif; ?>
<script>
function toggleTheme() {
    const body = document.body;
    const btn = document.querySelector('.theme-toggle');
    const isDark = body.classList.contains('dark-mode');
    body.classList.toggle('dark-mode');
    btn.textContent = isDark ? '🌙' : '☀️';
    localStorage.setItem('darkMode', isDark ? 'disabled' : 'enabled');
    console.log('Dark mode toggled. isDark:', isDark, 'Body classes:', body.className);
}
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    document.querySelector('.theme-toggle').textContent = '☀️';
    console.log('Dark mode enabled from storage');
}
console.log('Page loaded. Body classes:', document.body.className);
</script>
</body>
</html>