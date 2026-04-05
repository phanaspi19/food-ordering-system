<?php
require_once '../config.php';
require_once '../config/auth.php';

requireAdmin();

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT image FROM foods WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $food = $stmt->fetch();
    if ($food['image'] && file_exists('../uploads/' . $food['image'])) {
        unlink('../uploads/' . $food['image']);
    }
    
    $stmt = $conn->prepare("DELETE FROM foods WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: foods.php");
exit();
?>