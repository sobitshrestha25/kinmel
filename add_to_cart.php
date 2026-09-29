<?php
// add_to_cart.php - insert the item, or raise its quantity, then return JSON
require 'includes/db.php';
require 'includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(["status" => "error", "message" => "Please log in first."]);
    exit();
}

$userId = (int) $_SESSION['user_id'];
$productId = (int) ($_POST['product_id'] ?? 0);
$qty = max(1, (int) ($_POST['quantity'] ?? 1));

// Confirm the product exists and has stock
$stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(["status" => "error", "message" => "Product not found."]);
    exit();
}
if ($product['stock'] < $qty) {
    echo json_encode(["status" => "error", "message" => "Insufficient stock."]);
    exit();
}

$stmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
    $stmt->bind_param("ii", $qty, $existing['cart_id']);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $userId, $productId, $qty);
    $stmt->execute();
}

echo json_encode(["status" => "success", "total_items" => cartCount($conn, $userId)]);
