<?php
/*
    Admin Header
    -------------------------
    Included at the top of every admin page. Shows the sidebar.
    Protects the page - only logged-in admins can see this.
*/
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

// Figure out which sidebar link is "active" based on the current file name
$current_page = basename($_SERVER['PHP_SELF']);
function navActive($page, $current) {
    return $page === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - KinMel</title>
    <link rel="stylesheet" href="/kinmel/css/style.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <img src="/kinmel/images/logo-nav.png" alt="KinMel">
        </div>
        <nav class="admin-nav">
            <a href="/kinmel/admin/index.php" class="<?php echo navActive('index.php', $current_page); ?>">Dashboard</a>
            <a href="/kinmel/admin/products.php" class="<?php echo navActive('products.php', $current_page); ?>">Products</a>
            <a href="/kinmel/admin/categories.php" class="<?php echo navActive('categories.php', $current_page); ?>">Categories</a>
            <a href="/kinmel/admin/orders.php" class="<?php echo navActive('orders.php', $current_page); ?>">Orders</a>
            <a href="/kinmel/admin/users.php" class="<?php echo navActive('users.php', $current_page); ?>">Users</a>
            <a href="/kinmel/admin/profile.php" class="<?php echo navActive('profile.php', $current_page); ?>">Profile</a>
            <a href="/kinmel/logout.php" class="admin-logout">Logout</a>
        </nav>
    </aside>

    <main class="admin-main">
