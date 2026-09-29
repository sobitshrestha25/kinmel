<?php
// admin/manage_products.php - list all products with edit/delete actions
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$products = $conn->query("SELECT p.*, c.name AS category_name FROM products p
                           JOIN categories c ON p.category_id = c.category_id
                           ORDER BY p.created_at DESC");

$pageTitle = "Manage Products";
require '../includes/admin_header.php';
?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h1 class="section-title" style="margin:0;">Manage Products</h1>
    <a href="add_product.php" class="btn" style="background:#2563eb; color:#fff; text-decoration:none;">+ Add Product</a>
</div>

<div class="card-box">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($p = $products->fetch_assoc()): ?>
            <tr>
                <td><img src="../uploads/<?php echo $p['image'] ? sanitize($p['image']) : 'placeholder.jpg'; ?>" style="width:40px; height:40px; object-fit:cover; border-radius:4px;" onerror="this.src='https://via.placeholder.com/40'"></td>
                <td><?php echo sanitize($p['name']); ?></td>
                <td><?php echo sanitize($p['category_name']); ?></td>
                <td><?php echo formatPrice($p['price']); ?></td>
                <td><?php echo $p['stock']; ?></td>
                <td>
                    <a href="edit_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-outline" style="font-size:12px;">Edit</a>
                    <a href="delete_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-red" style="font-size:12px;"
                       onclick="return confirm('Delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require '../includes/admin_footer.php'; ?>
