<?php
require_once '../config.php';
require_once '../config/auth.php';

requireAdmin();

$stmt = $conn->query("SELECT profile_image FROM users WHERE id = " . $_SESSION['user_id']);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

$stmt = $conn->query("SELECT COUNT(*) as total FROM orders");
$totalOrders = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE status = 'delivered'");
$totalIncome = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses");
$totalExpenses = $stmt->fetch()['total'];

$profit = $totalIncome - $totalExpenses;

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$totalCustomers = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM foods");
$totalFoods = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE(order_date) = CURDATE()");
$todayOrders = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$pendingOrders = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'delivered'");
$deliveredOrders = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT f.name, COUNT(oi.id) as total FROM order_items oi JOIN foods f ON oi.food_id = f.id GROUP BY f.id ORDER BY total DESC LIMIT 5");
$popularFoods = $stmt->fetchAll();

$stmt = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 10");
$recentOrders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="profile.php" style="text-decoration: none; color: white;">
                    <h2>Admin Panel</h2>
                </a>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="foods.php">Foods</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="expenses.php">Expenses</a></li>
                <li><a href="money_tracking.php">Money Tracking</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Dashboard</h1>
                <div class="top-bar-right">
                    <button class="theme-toggle" onclick="toggleTheme()" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:8px 12px;border-radius:5px;cursor:pointer;font-size:16px;">🌙</button>
                    <a href="profile.php" class="user-profile">
                        <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="../uploads/profiles/<?= $_SESSION['profile_image'] ?>" alt="Profile" class="profile-img">
                        <?php else: ?>
                        <div class="profile-placeholder"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <span><?= $_SESSION['username'] ?></span>
                    </a>
                </div>
            </div>

            <?php if (isset($message)): ?>
            <div class="success-message"><?= $message ?></div>
            <?php endif; ?>

            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon">📋</div>
                    <div class="card-info">
                        <h3>Total Orders</h3>
                        <p class="card-number"><?= $totalOrders ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">📅</div>
                    <div class="card-info">
                        <h3>Today</h3>
                        <p class="card-number"><?= $todayOrders ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">⏳</div>
                    <div class="card-info">
                        <h3>Pending</h3>
                        <p class="card-number"><?= $pendingOrders ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">✅</div>
                    <div class="card-info">
                        <h3>Delivered</h3>
                        <p class="card-number"><?= $deliveredOrders ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">👥</div>
                    <div class="card-info">
                        <h3>Customers</h3>
                        <p class="card-number"><?= $totalCustomers ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">🍽️</div>
                    <div class="card-info">
                        <h3>Foods</h3>
                        <p class="card-number"><?= $totalFoods ?></p>
                    </div>
                </div>
            </div>

            <div class="money-cards">
                <div class="money-card income">
                    <h3>Income</h3>
                    <p class="money-amount">$<?= number_format($totalIncome, 2) ?></p>
                </div>
                <div class="money-card expense">
                    <h3>Expense</h3>
                    <p class="money-amount">$<?= number_format($totalExpenses, 2) ?></p>
                </div>
                <div class="money-card profit">
                    <h3>Profit</h3>
                    <p class="money-amount">$<?= number_format($profit, 2) ?></p>
                </div>
            </div>

            <div class="recent-orders">
                <h2>Recent Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= $order['username'] ?></td>
                            <td>$<?= number_format($order['total_price'], 2) ?></td>
                            <td><span class="status-<?= $order['status'] ?>"><?= $order['status'] ?></span></td>
                            <td><?= date('d/m/Y', strtotime($order['order_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($popularFoods)): ?>
            <div class="recent-orders">
                <h2>Popular Foods</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Food</th>
                            <th>Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popularFoods as $food): ?>
                        <tr>
                            <td><?= $food['name'] ?></td>
                            <td><?= $food['total'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
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
        console.log('Dark mode:', !isDark);
    }
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        document.querySelector('.theme-toggle').textContent = '☀️';
    }
    </script>
</body>
</html>