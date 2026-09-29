<?php
// order_success.php - order confirmation shown after checkout
require 'includes/db.php';
require 'includes/functions.php';
requireLogin();

$orderId = (int) ($_GET['order_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Order Confirmed";
require 'includes/header.php';
?>
<div class="card-box" style="text-align:center; max-width:480px; margin:0 auto;">
    <h1 style="color:#16a34a; font-size:22px; margin-bottom:10px;">✔ Order Placed Successfully</h1>
    <p style="color:#6b7280; margin-bottom:14px;">Thank you for shopping with kinMel.</p>
    <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
    <p><strong>Total:</strong> <?php echo formatPrice($order['total']); ?></p>
    <p><strong>Status:</strong> <span class="badge badge-pending"><?php echo ucfirst($order['status']); ?></span></p>
    <a href="my_orders.php" class="btn btn-primary" style="display:inline-block; margin-top:18px; text-decoration:none;">View My Orders</a>
</div>
<?php require 'includes/footer.php'; ?>
