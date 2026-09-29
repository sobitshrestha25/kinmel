<?php
// remove_from_cart.php - delete one line item from the logged in user's cart
require 'includes/db.php';
require 'includes/functions.php';
requireLogin();

$userId = (int) $_SESSION['user_id'];
$cartId = (int) ($_GET['cart_id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
$stmt->bind_param("ii", $cartId, $userId);
$stmt->execute();

header("Location: cart.php");
exit();
