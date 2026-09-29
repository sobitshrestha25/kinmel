<?php
// admin/dashboard.php - summary figures and recent orders for the admin
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();
$stats = $conn->query("SELECT COUNT(*) AS total_orders, IFNULL(SUM(total),0) AS total_sales FROM orders")->fetch_assoc();
$productCount = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$customerCount = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'customer'")->fetch_assoc()['c'];
$recentOrders = $conn->query("SELECT o.order_id, u.name, o.total, o.status, o.order_date
                               FROM orders o JOIN users u ON o.user_id = u.user_id
                               ORDER BY o.order_date DESC LIMIT 5");
$pageTitle = "Dashboard";
require '../includes/admin_header.php';
?>
<h1 class="section-title">Dashboard</h1>
<div class="stat-cards">
    <div class="stat-card"><div class="label">Total Orders</div><div class="value"><?php echo $stats['total_orders']; ?></div></div>
    <div class="stat-card"><div class="label">Total Sales</div><div class="value"><?php echo formatPrice($stats['total_sales']); ?></div></div>
    <div class="stat-card"><div class="label">Products</div><div class="value"><?php echo $productCount; ?></div></div>
    <div class="stat-card"><div class="label">Customers</div><div class="value"><?php echo $customerCount; ?></div></div>
</div>
<div class="card-box">
    <h2 class="section-title">Recent Orders</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php while ($o = $recentOrders->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $o['order_id']; ?></td>
                <td><?php echo sanitize($o['name']); ?></td>
                <td><?php echo formatPrice($o['total']); ?></td>
                <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                <td><?php echo date("d M Y", strtotime($o['order_date'])); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require '../includes/admin_footer.php'; ?>
