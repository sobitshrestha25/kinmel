<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
if (isAdmin()) redirect('/kinmel/admin/index.php');

$user_id = $_SESSION['user_id'];

// Only fetch orders belonging to the logged-in customer
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

require_once __DIR__ . '/includes/header.php';
?>

<h1>My Orders</h1>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Your order was placed successfully!</div>
<?php endif; ?>

<?php if ($orders->num_rows > 0): ?>
    <?php while ($order = $orders->fetch_assoc()): ?>
        <div class="order-card">
            <div class="order-card-header">
                <div>
                    <strong>Order #<?php echo (int)$order['id']; ?></strong>
                    <span class="order-date"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <span class="badge badge-<?php echo strtolower($order['status']); ?>"><?php echo clean($order['status']); ?></span>
            </div>

            <?php
            $itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemStmt->bind_param("i", $order['id']);
            $itemStmt->execute();
            $items = $itemStmt->get_result();
            ?>
            <table>
                <thead>
                    <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo clean($item['product_name']); ?></td>
                            <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo (int)$item['quantity']; ?></td>
                            <td>Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="order-card-footer">
                <strong>Total: Rs. <?php echo number_format($order['total_amount'], 2); ?></strong>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>You haven't placed any orders yet. <a href="/kinmel/products.php">Start shopping</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
