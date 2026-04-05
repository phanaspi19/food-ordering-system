<?php
require_once '../config.php';
require_once '../config/auth.php';

requireAdmin();

$stmt = $conn->query("SELECT profile_image FROM users WHERE id = " . $_SESSION['user_id']);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

$stmt = $conn->query("SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders</title>
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
                <li><a href="foods.php">Foods</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="expenses.php">Expenses</a></li>
                <li><a href="money_tracking.php">Money Tracking</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Orders</h1>
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

            <div class="table-container">
                <?php if (empty($orders)): ?>
                    <p class="empty-cart">No orders yet!</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= $order['username'] ?></td>
                                <td>$<?= number_format($order['total_price'], 2) ?></td>
                                <td>
                                    <span class="status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-select">
                                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="preparing" <?= $order['status'] == 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                            <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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