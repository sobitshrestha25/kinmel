<?php
// wishlist.php - products the customer has saved for later
require 'includes/db.php';
require 'includes/functions.php';
requireLogin();

$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT w.wishlist_id, p.product_id, p.name, p.price, p.image, p.stock
                         FROM wishlist w JOIN products p ON w.product_id = p.product_id
                         WHERE w.user_id = ? ORDER BY w.added_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$items = $stmt->get_result();

$pageTitle = "Wishlist";
require 'includes/header.php';
?>
<h1 style="font-size:20px; margin-bottom:16px;">Your Wishlist</h1>

<?php if ($items->num_rows === 0): ?>
    <div class="empty-state">Your wishlist is empty. <a href="index.php">Browse products</a></div>
<?php else: ?>
<div class="product-grid">
    <?php while ($p = $items->fetch_assoc()): ?>
        <div class="product-card">
            <img src="uploads/<?php echo $p['image'] ? sanitize($p['image']) : 'placeholder.jpg'; ?>" alt="<?php echo sanitize($p['name']); ?>" onerror="this.src='https://via.placeholder.com/220x150?text=kinMel'">
            <h3><?php echo sanitize($p['name']); ?></h3>
            <div class="price"><?php echo formatPrice($p['price']); ?></div>
            <div class="product-actions">
                <button class="btn-sm btn-add" onclick="addToCart(<?php echo $p['product_id']; ?>)" <?php echo $p['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo $p['stock'] > 0 ? 'Add to Cart' : 'Sold Out'; ?>
                </button>
                <a href="remove_from_wishlist.php?wishlist_id=<?php echo $p['wishlist_id']; ?>" class="btn-sm btn-wish" style="text-decoration:none; text-align:center;">Remove</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
