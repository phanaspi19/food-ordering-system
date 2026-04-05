<?php
require_once 'config.php';

try {
    $conn->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
    echo "Column added!";
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate') !== false) {
        echo "Column already exists!";
    } else {
        echo "Error: " . $e->getMessage();
    }
}