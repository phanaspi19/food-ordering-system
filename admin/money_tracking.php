<?php
require_once '../config.php';
require_once '../config/auth.php';

requireAdmin();

$stmt = $conn->query("SELECT profile_image FROM users WHERE id = " . $_SESSION['user_id']);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

$period = $_GET['period'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$date_condition = "";
if ($period == 'today') {
    $date_condition = "AND DATE(order_date) = CURDATE()";
} elseif ($period == 'month') {
    $date_condition = "AND MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())";
} elseif ($period == 'custom' && $start_date && $end_date) {
    $date_condition = "AND DATE(order_date) BETWEEN '$start_date' AND '$end_date'";
}

$income_sql = "SELECT COALESCE(SUM(total_price), 0) as income FROM orders WHERE status = 'delivered' $date_condition";
$stmt = $conn->query($income_sql);
$income = $stmt->fetch()['income'];

if ($period == 'today') {
    $expense_sql = "SELECT COALESCE(SUM(amount), 0) as expense FROM expenses WHERE date = CURDATE()";
} elseif ($period == 'month') {
    $expense_sql = "SELECT COALESCE(SUM(amount), 0) as expense FROM expenses WHERE MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())";
} elseif ($period == 'custom' && $start_date && $end_date) {
    $expense_sql = "SELECT COALESCE(SUM(amount), 0) as expense FROM expenses WHERE date BETWEEN '$start_date' AND '$end_date'";
} else {
    $expense_sql = "SELECT COALESCE(SUM(amount), 0) as expense FROM expenses";
}
$stmt = $conn->query($expense_sql);
$expense = $stmt->fetch()['expense'];

$profit = $income - $expense;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Money Tracking</title>
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
                <li><a href="orders.php">Orders</a></li>
                <li><a href="expenses.php">Expenses</a></li>
                <li><a href="money_tracking.php" class="active">Money Tracking</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Money Tracking</h1>
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

            <div class="period-filter">
                <a href="?period=all" class="<?= $period == 'all' ? 'active' : '' ?>">All</a>
                <a href="?period=today" class="<?= $period == 'today' ? 'active' : '' ?>">Today</a>
                <a href="?period=month" class="<?= $period == 'month' ? 'active' : '' ?>">This Month</a>
                <a href="?period=custom" class="<?= $period == 'custom' ? 'active' : '' ?>">Custom</a>
            </div>

            <?php if ($period == 'custom'): ?>
            <form method="GET" class="date-filter-form">
                <input type="hidden" name="period" value="custom">
                <input type="date" name="start_date" value="<?= $start_date ?>" required>
                <span>to</span>
                <input type="date" name="end_date" value="<?= $end_date ?>" required>
                <button type="submit" class="btn-primary">Search</button>
            </form>
            <?php endif; ?>

            <div class="money-cards">
                <div class="money-card income">
                    <h3>Income</h3>
                    <p class="money-amount">$<?= number_format($income, 2) ?></p>
                </div>
                <div class="money-card expense">
                    <h3>Expense</h3>
                    <p class="money-amount">$<?= number_format($expense, 2) ?></p>
                </div>
                <div class="money-card profit">
                    <h3>Profit</h3>
                    <p class="money-amount">$<?= number_format($profit, 2) ?></p>
                </div>
            </div>

            <div class="formula-box">
                <strong>Formula: Profit = Income - Expense = $<?= number_format($income, 2) ?> - $<?= number_format($expense, 2) ?> = $<?= number_format($profit, 2) ?></strong>
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