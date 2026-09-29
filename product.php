<?php
// product.php - view details of a single product
require 'includes/db.php';
require 'includes/functions.php';

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p
                         JOIN categories c ON p.category_id = c.category_id
                         WHERE p.product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit();
}

$pageTitle = $product['name'];
require 'includes/header.php';
?>
<div class="card-box" style="display:flex; gap:30px; flex-wrap:wrap;">
    <img src="uploads/<?php echo $product['image'] ? sanitize($product['image']) : 'placeholder.jpg'; ?>"
         alt="<?php echo sanitize($product['name']); ?>" style="width:280px; height:280px; object-fit:cover; border-radius:8px;"
         onerror="this.src='https://via.placeholder.com/280x280?text=kinMel'">
    <div style="flex:1; min-width:250px;">
        <span class="cat-tag"><?php echo sanitize($product['category_name']); ?></span>
        <h1 style="margin:6px 0;"><?php echo sanitize($product['name']); ?></h1>
        <div class="price" style="font-size:22px; margin-bottom:12px;"><?php echo formatPrice($product['price']); ?></div>
        <p style="color:#6b7280; margin-bottom:16px;"><?php echo nl2br(sanitize($product['description'])); ?></p>
        <p style="margin-bottom:16px; font-size:14px;">
            Stock: <?php echo $product['stock'] > 0 ? $product['stock'] . " available" : "<span style='color:#dc2626;'>Out of stock</span>"; ?>
        </p>
        <div class="product-actions" style="max-width:320px;">
            <?php if (isLoggedIn()): ?>
                <?php if ($product['stock'] > 0): ?>
                    <button class="btn-sm btn-add" onclick="addToCart(<?php echo $product['product_id']; ?>)">Add to Cart</button>
                <?php else: ?>
                    <button class="btn-sm btn-add" disabled>Sold Out</button>
                <?php endif; ?>
                <button class="btn-sm btn-wish" onclick="addToWishlist(<?php echo $product['product_id']; ?>)">Add to Wishlist</button>
            <?php else: ?>
                <a href="login.php" class="btn-sm btn-add" style="text-decoration:none; text-align:center;">Login to Buy</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
