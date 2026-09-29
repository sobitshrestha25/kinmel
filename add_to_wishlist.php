<?php
// add_to_wishlist.php - saving a product to the wishlist
require 'includes/db.php';
require 'includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(["status" => "error", "message" => "Please log in first."]);
    exit();
}

$userId = (int) $_SESSION['user_id'];
$productId = (int) ($_POST['product_id'] ?? 0);

$stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["status" => "success", "message" => "This product is already in your wishlist."]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();

echo json_encode(["status" => "success", "message" => "Added to wishlist."]);
