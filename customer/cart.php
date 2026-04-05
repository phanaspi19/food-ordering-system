<?php
require_once '../config.php';
require_once '../config/auth.php';

requireLogin();

$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order'])) {
    if (!empty($_SESSION['cart'])) {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $total]);
        $order_id = $conn->lastInsertId();
        
        foreach ($_SESSION['cart'] as $food_id => $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, food_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $food_id, $item['quantity'], $item['price']]);
        }
        
        $_SESSION['cart'] = [];
        $success = 'Order placed successfully!';
    }
}

if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: cart.php");
    exit();
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Online Food Ordering</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="../css/customer.css">
    <style>
body.dark-mode { background-color: #1a1a2e !important; }
body.dark-mode .navbar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important; }
body.dark-mode .container { background-color: #1a1a2e !important; }
body.dark-mode h1 { color: #fff !important; }
body.dark-mode .cart-table { background-color: #242442 !important; color: #fff !important; }
body.dark-mode .cart-table th { background-color: #4a4a70 !important; }
body.dark-mode .cart-table td { color: #fff !important; border-color: #3a3a5a !important; }
body.dark-mode .empty-cart { background-color: #242442 !important; color: #a0a0b0 !important; }
</style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Food Order</div>
        <div class="nav-links">
            <a href="foods.php">Foods</a>
            <a href="cart.php" class="active">Cart</a>
            <a href="my_orders.php">Orders</a>
            <a href="profile.php" class="user-profile">
                <span><?= $_SESSION['username'] ?></span>
            </a>
            <button class="theme-toggle" onclick="toggleTheme()" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:8px 12px;border-radius:5px;cursor:pointer;font-size:16px;">🌙</button>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>My Cart</h1>
        
        <?php if ($success): ?>
            <script src="../js/toast.js"></script>
            <script>showSuccess('<?= $success ?>')</script>
        <?php endif; ?>

        <?php if (empty($_SESSION['cart'])): ?>
            <p class="empty-cart">Cart is empty!</p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Food</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $food_id => $item): ?>
                    <?php $total += $item['price'] * $item['quantity']; ?>
                    <tr>
                        <td><?= $item['name'] ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        <td>
                            <a href="cart.php?remove=<?= $food_id ?>" class="btn-remove">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total:</strong></td>
                        <td><strong>$<?= number_format($total, 2) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <form method="POST" style="text-align: center; margin-top: 20px;">
                <button type="submit" name="order" class="btn-primary">Place Order</button>
            </form>
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