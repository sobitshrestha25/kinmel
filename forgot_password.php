<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
if (isLoggedIn()) {
    redirect(isAdmin() ? '/kinmel/admin/index.php' : '/kinmel/home.php');
}
$errors = [];
$success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        // Always show the same success message whether or not the email
        // exists, so people can't use this form to discover registered emails.
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $otp = strval(random_int(100000, 999999));
            $expires = date('Y-m-d H:i:s', time() + 600); // 10 minutes

            $update = $conn->prepare("UPDATE users SET reset_otp = ?, reset_otp_expires = ? WHERE id = ?");
            $update->bind_param("ssi", $otp, $expires, $user['id']);
            $update->execute();
            sendOtpEmail($email, $user['first_name'], $otp);
        }
        $success = "If that email is registered, a 6-digit code has been sent to it. The code expires in 10 minutes.";
        $_SESSION['reset_email'] = $email;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - KinMel</title>
    <link rel="stylesheet" href="/kinmel/css/style.css">
</head>
<body class="auth-body">
<div class="form-page">
    <div class="form-card form-card-small">
        <a href="/kinmel/index.php"><img src="/kinmel/images/logo-full.png" alt="KinMel" class="form-logo"></a>
        <h2>Forgot Password</h2>
        <p>Enter your account email and we'll send you a 6-digit reset code.</p>
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
            <p class="form-footer-text"><a href="/kinmel/reset_password.php">Enter your code &rarr;</a></p>
        <?php else: ?>
            <form method="POST" action="forgot_password.php">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Reset Code</button>
            </form>
        <?php endif; ?>
        <p class="form-footer-text"><a href="/kinmel/login.php">Back to login</a></p>
    </div>
</div>
</body>
</html>
