<?php
require_once '../config.php';
require_once '../config/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['food_id'])) {
    $food_id = $_POST['food_id'];
    $stmt = $conn->prepare("SELECT * FROM foods WHERE id = ?");
    $stmt->execute([$food_id]);
    $food = $stmt->fetch();

    if ($food) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$food_id])) {
            $_SESSION['cart'][$food_id]['quantity']++;
        } else {
            $_SESSION['cart'][$food_id] = [
                'name' => $food['name'],
                'price' => $food['price'],
                'quantity' => 1
            ];
        }
    }
    header("Location: foods.php?added=1");
    exit();
}
?>