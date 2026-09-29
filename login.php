<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, send to the right dashboard
if (isLoggedIn()) {
    redirect(dashboardUrl());
}

$errors = [];
$old_email = "";

// Message left by another page (e.g. "your account was blocked")
$flash_error = "";
if (!empty($_SESSION['flash']) && $_SESSION['flash'][0] === 'error') {
    $flash_error = $_SESSION['flash'][1];
    unset($_SESSION['flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $old_email = $email;

    if ($email === '' || $password === '') {
        $errors[] = "Please enter both email and password.";
    } else {
        // FIXED: the users table columns are id and first_name (not user_id / name)
        $stmt = $conn->prepare("SELECT id, first_name, password, role FROM users WHERE email = ?");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role']       = $user['role'];

                redirect(dashboardUrl());
            } else {
                $errors[] = "Incorrect email or password.";
            }
        } else {
            $errors[] = "Incorrect email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KinMel</title>
    <link rel="stylesheet" href="/kinmel/css/style.css">
</head>
<body class="auth-body">

<div class="form-page">
    <div class="form-card form-card-small">
        <a href="/kinmel/index.php"><img src="/kinmel/images/logo-full.png" alt="KinMel" class="form-logo"></a>

        <?php if ($flash_error): ?>
            <div class="alert alert-error"><?php echo clean($flash_error); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo clean($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo clean($old_email); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                Login
            </button>
        </form>

        <p class="form-footer-text">
            Don't have an account? <a href="/kinmel/register.php">Register here</a>
        </p>
    </div>
</div>

</body>
</html>