<?php
session_start();

$key = $_POST['key'] ?? '';

if (isset($_SESSION['cart'][$key])) {
    unset($_SESSION['cart'][$key]);
}

echo json_encode(['success' => true]);
