<?php
require_once '../config.php';
require_once '../config/auth.php';

requireLogin();

$stmt = $conn->prepare("SELECT o.* FROM orders o WHERE o.user_id = ? ORDER BY o.order_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

function getOrderItems($conn, $order_id) {
    $stmt = $conn->prepare("SELECT oi.*, f.name, f.image FROM order_items oi JOIN foods f ON oi.food_id = f.id WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Online Food Ordering</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="../css/customer.css">
    <style>
body.dark-mode { background-color: #1a1a2e !important; }
body.dark-mode .navbar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important; }
body.dark-mode .container { background-color: #1a1a2e !important; }
body.dark-mode h1 { color: #fff !important; }
body.dark-mode .order-card { background-color: #242442 !important; color: #fff !important; }
body.dark-mode .order-id { color: #fff !important; }
body.dark-mode .empty-cart { background-color: #242442 !important; color: #a0a0b0 !important; }
</style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Food Order</div>
        <div class="nav-links">
            <a href="foods.php">Foods</a>
            <a href="cart.php">Cart</a>
            <a href="my_orders.php" class="active">Orders</a>
            <a href="profile.php" class="user-profile">
                <span><?= $_SESSION['username'] ?></span>
            </a>
            <button class="theme-toggle" onclick="toggleTheme()" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:8px 12px;border-radius:5px;cursor:pointer;font-size:16px;">🌙</button>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>My Orders</h1>
        
        <?php if (empty($orders)): ?>
            <p class="empty-cart">No orders yet!</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-id">Order #<?= $order['id'] ?></div>
                    <div class="order-date"><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></div>
                    <span class="status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                </div>
                <div class="order-items">
                    <?php 
                    $items = getOrderItems($conn, $order['id']);
                    foreach ($items as $item): 
                    ?>
                    <div class="order-item">
                        <?php if ($item['image']): ?>
                            <img src="../uploads/<?= $item['image'] ?>" class="item-thumb">
                        <?php else: ?>
                            <div class="item-thumb-placeholder">🍽️</div>
                        <?php endif; ?>
                        <div class="item-info">
                            <div class="item-name"><?= $item['name'] ?></div>
                            <div class="item-qty">x<?= $item['quantity'] ?></div>
                        </div>
                        <div class="item-price">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="order-total">
                    <strong>Total: $<?= number_format($order['total_price'], 2) ?></strong>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
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