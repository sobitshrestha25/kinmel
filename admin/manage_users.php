<?php
// admin/manage_users.php - list registered customers and admins
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

$pageTitle = "Manage Users";
require '../includes/admin_header.php';
?>
<h1 class="section-title">Manage Users</h1>

<div class="card-box">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo sanitize($u['name']); ?></td>
                <td><?php echo sanitize($u['email']); ?></td>
                <td><?php echo sanitize($u['phone']); ?></td>
                <td><span class="badge <?php echo $u['role'] === 'admin' ? 'badge-shipped' : 'badge-completed'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                <td><?php echo date("d M Y", strtotime($u['created_at'])); ?></td>
                <td>
                    <?php if ($u['role'] !== 'admin'): ?>
                        <a href="delete_user.php?id=<?php echo $u['user_id']; ?>" class="btn btn-red" style="font-size:12px;"
                           onclick="return confirm('Delete this user?');">Delete</a>
                    <?php else: ?>
                        &mdash;
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require '../includes/admin_footer.php'; ?>
