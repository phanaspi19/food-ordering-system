<?php
require_once '../config.php';
require_once '../config/auth.php';

requireAdmin();

$stmt = $conn->query("SELECT profile_image FROM users WHERE id = " . $_SESSION['user_id']);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $description = trim($_POST['description']);
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    
    $stmt = $conn->prepare("INSERT INTO expenses (description, amount, date) VALUES (?, ?, ?)");
    $stmt->execute([$description, $amount, $date]);
}

if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: expenses.php");
    exit();
}

$stmt = $conn->query("SELECT * FROM expenses ORDER BY date DESC");
$expenses = $stmt->fetchAll();

$total_expense = array_sum(array_column($expenses, 'amount'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Expenses</title>
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
                <li><a href="expenses.php" class="active">Expenses</a></li>
                <li><a href="money_tracking.php">Money Tracking</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Manage Expenses</h1>
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

            <div class="add-expense-form">
                <h3>+ Add Expense</h3>
                <form method="POST">
                    <input type="text" name="description" placeholder="Description" required>
                    <input type="number" name="amount" placeholder="Amount ($)" step="0.01" required>
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
                    <button type="submit" name="add_expense" class="btn-primary">Add</button>
                </form>
            </div>

            <div class="total-expense">
                <strong>Total Expense: $<?= number_format($total_expense, 2) ?></strong>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?= $expense['id'] ?></td>
                            <td><?= $expense['description'] ?></td>
                            <td>$<?= number_format($expense['amount'], 2) ?></td>
                            <td><?= date('d/m/Y', strtotime($expense['date'])) ?></td>
                            <td>
                                <a href="expenses.php?delete=<?= $expense['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
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