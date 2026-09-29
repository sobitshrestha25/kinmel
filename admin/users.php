<?php
require_once __DIR__ . '/includes/admin_header.php';

$users = $conn->query("SELECT u.*, 
                        (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count 
                        FROM users u WHERE u.role = 'customer' 
                        ORDER BY u.created_at DESC");
?>

<h1>Users</h1>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Country</th>
            <th>Orders</th>
            <th>Joined</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($users->num_rows > 0): ?>
            <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($u['first_name'] . ' ' . $u['last_name']); ?></td>
                    <td><?php echo clean($u['email']); ?></td>
                    <td><?php echo clean($u['phone']); ?></td>
                    <td><?php echo clean($u['country']); ?></td>
                    <td><?php echo (int)$u['order_count']; ?></td>
                    <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No registered customers yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
