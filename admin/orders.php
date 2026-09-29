<?php
require_once __DIR__ . '/includes/admin_header.php';

$message = "";

// ---------------- Update order status ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

    if (in_array($status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $message = "Order #$order_id status updated to $status.";
    }
}

// ---------------- Fetch all orders ----------------
$orders = $conn->query("SELECT o.*, u.first_name, u.last_name, u.email 
                         FROM orders o JOIN users u ON o.user_id = u.id 
                         ORDER BY o.created_at DESC");
?>

<h1>Orders</h1>

<?php if ($message): ?><div class="alert alert-success"><?php echo clean($message); ?></div><?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Update Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo (int)$order['id']; ?></td>
                    <td>
                        <?php echo clean($order['first_name'] . ' ' . $order['last_name']); ?><br>
                        <small><?php echo clean($order['email']); ?></small>
                    </td>
                    <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($order['status']); ?>"><?php echo clean($order['status']); ?></span></td>
                    <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                    <td>
                        <form method="POST" action="orders.php" class="status-form">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status">
                                <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo ($order['status'] === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-small btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No orders yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
