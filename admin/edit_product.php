<?php
// admin/edit_product.php - update an existing product's details
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$productId = (int) ($_GET['id'] ?? 0);
$error = "";

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: manage_products.php");
    exit();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if (isset($_POST['update_product'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $categoryId = (int) $_POST['category_id'];
    $image = $product['image'];

    if ($price <= 0) {
        $error = "Please enter a valid price.";
    } elseif ($stock < 0) {
        $error = "Please enter a valid stock quantity.";
    } else {
        if (!empty($_FILES['image']['name'])) {
            $image = time() . "_" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image);
        }

        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?
                                 WHERE product_id = ?");
        $stmt->bind_param("ssdissi", $name, $description, $price, $stock, $categoryId, $image, $productId);
        if ($stmt->execute()) {
            header("Location: manage_products.php");
            exit();
        } else {
            $error = "Failed to update product. Please try again.";
        }
    }
}

$pageTitle = "Edit Product";
require '../includes/admin_header.php';
?>
<h1 class="section-title">Edit Product</h1>

<div class="card-box" style="max-width:500px;">
    <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" onsubmit="return validateProductForm()">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" value="<?php echo sanitize($product['name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $c['category_id']; ?>" <?php echo $c['category_id'] == $product['category_id'] ? 'selected' : ''; ?>>
                        <?php echo sanitize($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?php echo sanitize($product['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Price (Rs.)</label>
            <input type="number" name="price" step="0.01" min="0.01" value="<?php echo $product['price']; ?>" required>
        </div>
        <div class="form-group">
            <label>Stock</label>
            <input type="number" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>
        </div>
        <div class="form-group">
            <label>Replace Image (optional)</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <button type="submit" name="update_product" class="btn-primary">Update Product</button>
    </form>
</div>
<script src="../js/validation.js"></script>
<?php require '../includes/admin_footer.php'; ?>
