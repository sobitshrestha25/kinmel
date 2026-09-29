<?php
// admin/update_order.php - changing the status of an order
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$orderId = (int) ($_POST['order_id'] ?? 0);
$status = sanitize($_POST['status'] ?? 'pending');
$allowed = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

if (in_array($status, $allowed)) {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $orderId);
    $stmt->execute();
}

header("Location: manage_orders.php");
exit();
