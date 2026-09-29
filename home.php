<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Admins shouldn't browse the customer home - send them to their own dashboard
if (isAdmin()) {
    redirect('/kinmel/admin/index.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero hero-compact">
    <h1>Welcome back, <?php echo clean($_SESSION['first_name']); ?>!</h1>
    <p>Browse products, check your cart, or track your orders.</p>
    <a href="/kinmel/products.php" class="btn btn-primary">Browse Products</a>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
