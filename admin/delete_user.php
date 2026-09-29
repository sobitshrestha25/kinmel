<?php
// admin/delete_user.php - remove a customer account
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$userId = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
$stmt->bind_param("i", $userId);
$stmt->execute();

header("Location: manage_users.php");
exit();
