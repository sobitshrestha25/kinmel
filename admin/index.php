<?php
require_once __DIR__ . '/includes/admin_header.php';

// ---------------- Statistics ----------------
$total_products = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$total_categories = $conn->query("SELECT COUNT(*) AS c FROM categories")->fetch_assoc()['c'];
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'customer'")->fetch_assoc()['c'];
$total_orders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$total_sales = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS s FROM orders WHERE status != 'Cancelled'")->fetch_assoc()['s'];

// ---------------- Recent orders ----------------
$recent_orders = $conn->query("SELECT o.*, u.first_name, u.last_name 
                                FROM orders o JOIN users u ON o.user_id = u.id 
                                ORDER BY o.created_at DESC LIMIT 8");
?>

<h1>Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-label">Total Products</span>
        <span class="stat-value"><?php echo (int)$total_products; ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Total Categories</span>
        <span class="stat-value"><?php echo (int)$total_categories; ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Total Users</span>
        <span class="stat-value"><?php echo (int)$total_users; ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Total Orders</span>
        <span class="stat-value"><?php echo (int)$total_orders; ?></span>
    </div>
    <div class="stat-card stat-card-highlight">
        <span class="stat-label">Total Sales</span>
        <span class="stat-value">Rs. <?php echo number_format($total_sales, 2); ?></span>
    </div>
</div>

<h2>Recent Orders</h2>
<table>
    <thead>
        <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($recent_orders->num_rows > 0): ?>
            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo (int)$order['id']; ?></td>
                    <td><?php echo clean($order['first_name'] . ' ' . $order['last_name']); ?></td>
                    <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($order['status']); ?>"><?php echo clean($order['status']); ?></span></td>
                    <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No orders yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
