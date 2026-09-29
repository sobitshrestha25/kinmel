<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
if (isAdmin()) redirect('/kinmel/admin/index.php');

$user_id = $_SESSION['user_id'];
$profile_message = "";
$profile_error = "";
$password_message = "";
$password_error = "";

// ---------------- Update profile details ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $address = trim($_POST['address']);

    if ($first_name === '' || $last_name === '' || $phone === '' || $country === '' || $address === '') {
        $profile_error = "All fields except middle name are required.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, phone=?, country=?, address=? WHERE id=?");
        $stmt->bind_param("ssssssi", $first_name, $middle_name, $last_name, $phone, $country, $address, $user_id);
        if ($stmt->execute()) {
            $_SESSION['first_name'] = $first_name;
            $profile_message = "Profile updated successfully.";
        } else {
            $profile_error = "Something went wrong. Please try again.";
        }
    }
}

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

// ---------------- Fetch current user data ----------------
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

require_once __DIR__ . '/includes/header.php';
?>

<h1>My Profile</h1>

<div class="profile-grid">
    <div class="form-card">
        <h2>Edit Details</h2>

        <?php if ($profile_message): ?><div class="alert alert-success"><?php echo clean($profile_message); ?></div><?php endif; ?>
        <?php if ($profile_error): ?><div class="alert alert-error"><?php echo clean($profile_error); ?></div><?php endif; ?>

        <form method="POST" action="profile.php">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo clean($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" value="<?php echo clean($user['middle_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?php echo clean($user['last_name']); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email (cannot be changed)</label>
                    <input type="email" value="<?php echo clean($user['email']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?php echo clean($user['phone']); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" value="<?php echo clean($user['country']); ?>" required>
                </div>
                <div class="form-group form-group-full">
                    <label>Address</label>
                    <input type="text" name="address" value="<?php echo clean($user['address']); ?>" required>
                </div>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
        </form>
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
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
