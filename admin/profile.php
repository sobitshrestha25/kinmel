<?php
require_once __DIR__ . '/includes/admin_header.php';

$user_id = $_SESSION['user_id'];
$password_message = "";
$password_error = "";

// ---------------- Change password ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!password_verify($current_password, $row['password'])) {
        $password_error = "Current password is incorrect.";
    } elseif (strlen($new_password) < 6) {
        $password_error = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_new_password) {
        $password_error = "New password and confirm password do not match.";
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $new_hash, $user_id);
        $update->execute();
        $password_message = "Password changed successfully.";
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
?>

<h1>Admin Profile</h1>

<div class="form-card">
    <h2>Account Details</h2>
    <p><strong>Name:</strong> <?php echo clean($admin['first_name'] . ' ' . $admin['last_name']); ?></p>
    <p><strong>Email:</strong> <?php echo clean($admin['email']); ?></p>
    <p><strong>Role:</strong> Administrator</p>
</div>

<div class="form-card">
    <h2>Change Password</h2>

    <?php if ($password_message): ?><div class="alert alert-success"><?php echo clean($password_message); ?></div><?php endif; ?>
    <?php if ($password_error): ?><div class="alert alert-error"><?php echo clean($password_error); ?></div><?php endif; ?>

    <form method="POST" action="profile.php">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="6">
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_new_password" required minlength="6">
        </div>
        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
