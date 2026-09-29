<?php
// admin/add_product.php - inserting a product together with its image
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$error = "";
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if (isset($_POST['add_product'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $categoryId = (int) $_POST['category_id'];
    $image = "";

    if ($price <= 0) {
        $error = "Please enter a valid price.";
    } elseif ($stock < 0) {
        $error = "Please enter a valid stock quantity.";
    } else {
        if (!empty($_FILES['image']['name'])) {
            $image = time() . "_" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image);
        }

        $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, stock, image)
                                 VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdis", $categoryId, $name, $description, $price, $stock, $image);
        if ($stmt->execute()) {
            header("Location: manage_products.php");
            exit();
        } else {
            $error = "Failed to add product. Please try again.";
        }
    }
}

$pageTitle = "Add Product";
require '../includes/admin_header.php';
?>
<h1 class="section-title">Add New Product</h1>

<div class="card-box" style="max-width:500px;">
    <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" onsubmit="return validateProductForm()">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $c['category_id']; ?>"><?php echo sanitize($c['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label>Price (Rs.)</label>
            <input type="number" name="price" step="0.01" min="0.01" required>
        </div>
        <div class="form-group">
            <label>Stock</label>
            <input type="number" name="stock" min="0" required>
        </div>
        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <button type="submit" name="add_product" class="btn-primary">Add Product</button>
    </form>
</div>
<script src="../js/validation.js"></script>
<?php require '../includes/admin_footer.php'; ?>
