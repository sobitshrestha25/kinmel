<?php
// admin/manage_orders.php - list every order and let the admin update its status
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$orders = $conn->query("SELECT o.order_id, u.name, u.email, o.total, o.status, o.order_date
                         FROM orders o JOIN users u ON o.user_id = u.user_id
                         ORDER BY o.order_date DESC");

$statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

$pageTitle = "Manage Orders";
require '../includes/admin_header.php';
?>
<h1 class="section-title">Manage Orders</h1>

<div class="card-box">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Date</th><th>Status</th><th>Update</th></tr></thead>
        <tbody>
        <?php while ($o = $orders->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $o['order_id']; ?></td>
                <td><?php echo sanitize($o['name']); ?><br><small style="color:#6b7280;"><?php echo sanitize($o['email']); ?></small></td>
                <td><?php echo formatPrice($o['total']); ?></td>
                <td><?php echo date("d M Y", strtotime($o['order_date'])); ?></td>
                <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                <td>
                    <form method="POST" action="update_order.php" style="display:flex; gap:6px;">
                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                        <select name="status">
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $o['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline" style="font-size:12px;">Save</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require '../includes/admin_footer.php'; ?>
