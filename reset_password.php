<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '/kinmel/admin/index.php' : '/kinmel/home.php');
}

$errors = [];
$success = "";
$old_email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $old_email = $email;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $otp === '') {
        $errors[] = "Please fill in all fields.";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id, reset_otp, reset_otp_expires FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            $errors[] = "Invalid code or email.";
        } else {
            $user = $result->fetch_assoc();

            if ($user['reset_otp'] === null || $user['reset_otp'] !== $otp) {
                $errors[] = "Invalid code or email.";
            } elseif (strtotime($user['reset_otp_expires']) < time()) {
                $errors[] = "This code has expired. Please request a new one.";
            } else {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ?, reset_otp = NULL, reset_otp_expires = NULL WHERE id = ?");
                $update->bind_param("si", $new_hash, $user['id']);
                $update->execute();

                unset($_SESSION['reset_email']);
                $success = "Password reset successfully. You can now login.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KinMel</title>
    <link rel="stylesheet" href="/kinmel/css/style.css">
</head>
<body class="auth-body">

<div class="form-page">
    <div class="form-card form-card-small">
        <a href="/kinmel/index.php"><img src="/kinmel/images/logo-full.png" alt="KinMel" class="form-logo"></a>
        <h2>Reset Password</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo clean($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo clean($success); ?></div>
            <p class="form-footer-text"><a href="/kinmel/login.php">Go to login &rarr;</a></p>
        <?php else: ?>
            <form method="POST" action="reset_password.php">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo clean($old_email); ?>" required>
                </div>
                <div class="form-group">
                    <label>6-Digit Code</label>
                    <input type="text" name="otp" maxlength="6" pattern="\d{6}" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
            <p class="form-footer-text"><a href="/kinmel/forgot_password.php">Didn't get a code? Request again</a></p>
        <?php endif; ?>

        <p class="form-footer-text"><a href="/kinmel/login.php">Back to login</a></p>
    </div>
</div>

</body>
</html>
