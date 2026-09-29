<?php
// update_cart.php - increase or decrease the quantity of one cart item
require 'includes/db.php';
require 'includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(["status" => "error", "message" => "Please log in first."]);
    exit();
}

$userId = (int) $_SESSION['user_id'];
$cartId = (int) ($_POST['cart_id'] ?? 0);
$change = (int) ($_POST['change'] ?? 0);

$stmt = $conn->prepare("SELECT c.quantity, p.stock FROM cart c
                         JOIN products p ON c.product_id = p.product_id
                         WHERE c.cart_id = ? AND c.user_id = ?");
$stmt->bind_param("ii", $cartId, $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Cart item not found."]);
    exit();
}

$newQty = $row['quantity'] + $change;

if ($newQty < 1) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ?");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
} elseif ($newQty > $row['stock']) {
    echo json_encode(["status" => "error", "message" => "Only " . $row['stock'] . " in stock."]);
    exit();
} else {
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $stmt->bind_param("ii", $newQty, $cartId);
    $stmt->execute();
}

echo json_encode(["status" => "success"]);
