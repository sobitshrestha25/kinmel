<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
if (isAdmin()) redirect('/kinmel/admin/index.php');

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";
$upload_dir = __DIR__ . '/uploads/products/';
$allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$max_size = 2 * 1024 * 1024; // 2MB

// Helper: handle image upload, returns filename or false
function handleImageUpload($file, $upload_dir, $allowed_types, $max_size, &$error) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no new file selected
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "There was an error uploading the image.";
        return false;
    }
    if (!in_array($file['type'], $allowed_types)) {
        $error = "Only JPG, PNG, GIF, or WEBP images are allowed.";
        return false;
    }
    if ($file['size'] > $max_size) {
        $error = "Image must be smaller than 2MB.";
        return false;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = uniqid('product_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
        $error = "Failed to save the uploaded image.";
        return false;
    }
    return $new_name;
}

// ---------------- Add product ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];

    if ($name === '' || $category_id <= 0 || $price <= 0 || $stock < 0) {
        $error = "Please fill all fields correctly.";
    } else {
        $image_name = handleImageUpload($_FILES['image'], $upload_dir, $allowed_types, $max_size, $error);

        if ($image_name === false) {
            // error already set
        } else {
            $image_to_save = $image_name ?? 'no-image.png';
            $stmt = $conn->prepare("INSERT INTO products (category_id, owner_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissdis", $category_id, $user_id, $name, $description, $price, $stock, $image_to_save);
            $stmt->execute();
            $message = "Product added successfully.";
        }
    }
}

// ---------------- Update product (only own products) ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = (int)$_POST['product_id'];

    // Ownership check
    $own = $conn->prepare("SELECT id FROM products WHERE id = ? AND owner_id = ?");
    $own->bind_param("ii", $id, $user_id);
    $own->execute();
    if ($own->get_result()->num_rows !== 1) {
        $error = "You can only edit products you added yourself.";
    } else {
        $name = trim($_POST['name']);
        $category_id = (int)$_POST['category_id'];
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];

        if ($name === '' || $category_id <= 0 || $price <= 0 || $stock < 0) {
            $error = "Please fill all fields correctly.";
        } else {
            $image_name = handleImageUpload($_FILES['image'], $upload_dir, $allowed_types, $max_size, $error);

            if ($image_name === false) {
                // error already set
            } else {
                if ($image_name !== null) {
                    $old = $conn->prepare("SELECT image FROM products WHERE id = ?");
                    $old->bind_param("i", $id);
                    $old->execute();
                    $oldRow = $old->get_result()->fetch_assoc();
                    if ($oldRow && $oldRow['image'] !== 'no-image.png' && file_exists($upload_dir . $oldRow['image'])) {
                        unlink($upload_dir . $oldRow['image']);
                    }

                    $stmt = $conn->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image=? WHERE id=? AND owner_id=?");
                    $stmt->bind_param("issdisii", $category_id, $name, $description, $price, $stock, $image_name, $id, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, stock=? WHERE id=? AND owner_id=?");
                    $stmt->bind_param("issdiii", $category_id, $name, $description, $price, $stock, $id, $user_id);
                }
                $stmt->execute();
                $message = "Product updated successfully.";
            }
        }
    }
}

// ---------------- Delete product (only own products) ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int)$_POST['product_id'];

    $own = $conn->prepare("SELECT image FROM products WHERE id = ? AND owner_id = ?");
    $own->bind_param("ii", $id, $user_id);
    $own->execute();
    $ownRow = $own->get_result()->fetch_assoc();

    if (!$ownRow) {
        $error = "You can only delete products you added yourself.";
    } else {
        if ($ownRow['image'] !== 'no-image.png' && file_exists($upload_dir . $ownRow['image'])) {
            unlink($upload_dir . $ownRow['image']);
        }
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND owner_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $message = "Product deleted successfully.";
    }
}

// ---------------- Load product to edit (if any, and owned by this user) ----------------
$editing_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $editing_product = $result->fetch_assoc();
    }
}

// ---------------- Categories for dropdown ----------------
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories_list = [];
while ($c = $categories->fetch_assoc()) $categories_list[] = $c;

// ---------------- Fetch only this user's own products ----------------
$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p
                         JOIN categories c ON p.category_id = c.id
                         WHERE p.owner_id = ?
                         ORDER BY p.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$products = $stmt->get_result();

require_once __DIR__ . '/includes/header.php';
?>

<h1>My Products</h1>
<p>Add, edit, or remove the products you're selling. Only products you added yourself show up here.</p>

<?php if ($message): ?><div class="alert alert-success"><?php echo clean($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo clean($error); ?></div><?php endif; ?>

<div class="form-card">
    <h2><?php echo $editing_product ? 'Edit Product' : 'Add New Product'; ?></h2>

    <?php if (count($categories_list) === 0): ?>
        <p>There are no categories yet. Please check back once an admin has added some.</p>
    <?php else: ?>
        <form method="POST" action="my_products.php" enctype="multipart/form-data">
            <?php if ($editing_product): ?>
                <input type="hidden" name="product_id" value="<?php echo $editing_product['id']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" value="<?php echo $editing_product ? clean($editing_product['name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories_list as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"
                                <?php echo ($editing_product && $editing_product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo clean($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price (Rs.)</label>
                    <input type="number" step="0.01" min="0" name="price" value="<?php echo $editing_product ? $editing_product['price'] : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Stock / Quantity</label>
                    <input type="number" min="0" name="stock" value="<?php echo $editing_product ? $editing_product['stock'] : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" value="<?php echo $editing_product ? clean($editing_product['description']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Product Image <?php echo $editing_product ? '(leave empty to keep current image)' : ''; ?></label>
                <input type="file" name="image" accept="image/*">
                <?php if ($editing_product): ?>
                    <img src="/kinmel/uploads/products/<?php echo clean($editing_product['image']); ?>"
                         onerror="this.src='/kinmel/images/no-image.png'" class="current-image-preview" alt="Current image">
                <?php endif; ?>
            </div>

            <?php if ($editing_product): ?>
                <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                <a href="/kinmel/my_products.php" class="btn btn-secondary-light">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($products->num_rows > 0): ?>
            <?php while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="/kinmel/uploads/products/<?php echo clean($p['image']); ?>"
                             onerror="this.src='/kinmel/images/no-image.png'" class="table-thumb" alt="<?php echo clean($p['name']); ?>">
                    </td>
                    <td><?php echo clean($p['name']); ?></td>
                    <td><?php echo clean($p['category_name']); ?></td>
                    <td>Rs. <?php echo number_format($p['price'], 2); ?></td>
                    <td><?php echo (int)$p['stock']; ?></td>
                    <td>
                        <a href="/kinmel/my_products.php?edit=<?php echo $p['id']; ?>" class="btn btn-small btn-secondary-light">Edit</a>
                        <form method="POST" action="my_products.php" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <button type="submit" name="delete_product" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">You haven't added any products yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
