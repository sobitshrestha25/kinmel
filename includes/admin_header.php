<?php
// includes/admin_header.php - shared header + sidebar for all admin pages
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? $pageTitle . " - kinMel Admin" : "kinMel Admin"; ?></title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="site-header">
    <div class="nav-container">
        <a href="dashboard.php" class="logo">🛍️ kinMel Admin</a>
        <nav class="nav-links" style="margin-left:auto;">
            <span>Hi, <?php echo sanitize($_SESSION['name']); ?></span>
            <a href="../index.php">View Store</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="page-content">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="manage_products.php" class="<?php echo in_array($currentPage, ['manage_products.php','add_product.php','edit_product.php']) ? 'active' : ''; ?>">Products</a>
        <a href="manage_categories.php" class="<?php echo $currentPage === 'manage_categories.php' ? 'active' : ''; ?>">Categories</a>
        <a href="manage_orders.php" class="<?php echo $currentPage === 'manage_orders.php' ? 'active' : ''; ?>">Orders</a>
        <a href="manage_users.php" class="<?php echo $currentPage === 'manage_users.php' ? 'active' : ''; ?>">Users</a>
    </aside>
    <div class="admin-main">
