<?php
/*
    Common Header
    -------------------------
    Included at the top of every customer-facing page.
    Shows the navigation bar. The "Categories" dropdown is
    built dynamically from the categories table in MySQL.
*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Fetch categories for the dropdown (dynamic, not hard-coded)
$nav_categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $nav_categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KINMEL - Online Store</title>
    <link rel="stylesheet" href="/kinmel/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-container">
        <a href="/kinmel/index.php" class="logo">
            <img src="/kinmel/images/logo-nav.png" alt="KinMel">
        </a>

        <nav class="main-nav">
            <ul>
                <li><a href="/kinmel/home.php">Home</a></li>
                <li><a href="/kinmel/products.php">Products</a></li>
                <li class="dropdown">
                    <a href="#">Categories &#9662;</a>
                    <ul class="dropdown-menu">
                        <?php if (count($nav_categories) > 0): ?>
                            <?php foreach ($nav_categories as $cat): ?>
                                <li>
                                    <a href="/kinmel/products.php?category_id=<?php echo (int)$cat['id']; ?>">
                                        <?php echo clean($cat['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><span>No categories yet</span></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <?php if (isLoggedIn()): ?>
                    <li><a href="/kinmel/cart.php">Cart</a></li>
                    <li><a href="/kinmel/my_orders.php">My Orders</a></li>
                    <li><a href="/kinmel/profile.php">Profile</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="/kinmel/admin/index.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="/kinmel/logout.php">Logout (<?php echo clean($_SESSION['first_name']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="/kinmel/login.php">Login</a></li>
                    <li><a href="/kinmel/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="site-main">
