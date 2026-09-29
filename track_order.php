<?php
// track_order.php - shows the status and items of a single order
require 'includes/db.php';
require 'includes/functions.php';
requireLogin();

$userId = (int) $_SESSION['user_id'];
$orderId = (int) ($_GET['order_id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: my_orders.php");
    exit();
}

$stmt = $conn->prepare("SELECT i.*, p.name, p.image FROM order_items i
                         JOIN products p ON i.product_id = p.product_id
                         WHERE i.order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result();

$stages = ['pending', 'processing', 'shipped', 'completed'];
$currentStage = array_search($order['status'], $stages);

$pageTitle = "Track Order #" . $orderId;
require 'includes/header.php';
?>
<h1 style="font-size:20px; margin-bottom:16px;">Order #<?php echo $orderId; ?></h1>

<div class="card-box">
    <?php if ($order['status'] === 'cancelled'): ?>
        <p><span class="badge badge-cancelled">Cancelled</span></p>
    <?php else: ?>
        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <?php foreach ($stages as $i => $stage): ?>
                <div style="text-align:center; flex:1;">
                    <div style="width:24px; height:24px; border-radius:50%; margin:0 auto 6px;
                                background:<?php echo $i <= $currentStage ? '#2563eb' : '#e5e7eb'; ?>;"></div>
                    <span style="font-size:12px; text-transform:capitalize;"><?php echo $stage; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <p><strong>Order Date:</strong> <?php echo date("d M Y, h:i A", strtotime($order['order_date'])); ?></p>
    <p><strong>Total:</strong> <?php echo formatPrice($order['total']); ?></p>
</div>

<div class="card-box">
    <h2 class="section-title">Items</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php while ($it = $items->fetch_assoc()): ?>
            <tr>
                <td><?php echo sanitize($it['name']); ?></td>
                <td><?php echo $it['quantity']; ?></td>
                <td><?php echo formatPrice($it['price']); ?></td>
                <td><?php echo formatPrice($it['subtotal']); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
