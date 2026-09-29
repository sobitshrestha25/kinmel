<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '/kinmel/admin/index.php' : '/kinmel/home.php');
}

$errors = [];
$success = "";

$old = [
    'first_name' => '', 'middle_name' => '', 'last_name' => '',
    'gender' => '', 'email' => '', 'phone' => '',
    'country' => '', 'address' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $gender = trim($_POST['gender']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $country = trim($_POST['country']);
    $address = trim($_POST['address']);

    $old = compact('first_name','middle_name','last_name','gender','email','phone','country','address');

    if ($first_name === '') $errors[] = "First name is required.";
    if ($last_name === '') $errors[] = "Last name is required.";
    if (!in_array($gender, ['Male','Female','Other'])) $errors[] = "Please select a valid gender.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if ($phone === '') $errors[] = "Phone number is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
    if ($country === '') $errors[] = "Country is required.";
    if ($address === '') $errors[] = "Address is required.";

    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errors[] = "This email is already registered. Please login instead.";
        }
        $check->close();
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users 
            (first_name, middle_name, last_name, gender, email, phone, password, country, address, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'customer')");
        $stmt->bind_param(
            "sssssssss",
            $first_name, $middle_name, $last_name, $gender,
            $email, $phone, $hashed_password, $country, $address
        );

        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
            $old = ['first_name'=>'','middle_name'=>'','last_name'=>'','gender'=>'','email'=>'','phone'=>'','country'=>'','address'=>''];
        } else {
            $errors[] = "Something went wrong. Please try again.";
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
    <title>Register - KinMel</title>
    <link rel="stylesheet" href="/kinmel/css/style.css">
</head>
<body class="auth-body">

<div class="form-page">
    <div class="form-card">
        <a href="/kinmel/index.php"><img src="/kinmel/images/logo-full.png" alt="KinMel" class="form-logo"></a>
        <h2>Create a Customer Account</h2>

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
            <div class="alert alert-success">
                <?php echo clean($success); ?>
                <a href="/kinmel/login.php">Click here to login</a>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" value="<?php echo clean($old['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" value="<?php echo clean($old['middle_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" value="<?php echo clean($old['last_name']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male" <?php echo ($old['gender']=='Male')?'selected':''; ?>>Male</option>
                        <option value="Female" <?php echo ($old['gender']=='Female')?'selected':''; ?>>Female</option>
                        <option value="Other" <?php echo ($old['gender']=='Other')?'selected':''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?php echo clean($old['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="text" name="phone" value="<?php echo clean($old['phone']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Country *</label>
                    <input type="text" name="country" value="<?php echo clean($old['country']); ?>" required>
                </div>
                <div class="form-group form-group-full">
                    <label>Address *</label>
                    <input type="text" name="address" value="<?php echo clean($old['address']); ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <p class="form-footer-text">Already have an account? <a href="/kinmel/login.php">Login here</a></p>
    </div>
</div>

</body>
</html>
