<?php
// admin/delete_category.php - remove a category (cascades to its products)
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$categoryId = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
$stmt->bind_param("i", $categoryId);
$stmt->execute();

header("Location: manage_categories.php");
exit();
