<?php
// admin/delete_product.php - remove a product from the catalogue
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$productId = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();

header("Location: manage_products.php");
exit();
