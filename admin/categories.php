<?php
require_once __DIR__ . '/includes/admin_header.php';

$message = "";
$error = "";

// ---------------- Add category ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name === '') {
        $error = "Category name is required.";
    } else {
        $check = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $check->bind_param("s", $name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "A category with this name already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
            $stmt->execute();
            $message = "Category added successfully.";
        }
    }
}

// ---------------- Update category ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name === '') {
        $error = "Category name is required.";
    } else {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);
        $stmt->execute();
        $message = "Category updated successfully.";
    }
}

// ---------------- Delete category ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $id = (int)$_POST['category_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "Category deleted successfully.";
}

// ---------------- Load category to edit (if any) ----------------
$editing_category = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $editing_category = $result->fetch_assoc();
    }
}

// ---------------- Fetch all categories ----------------
$categories = $conn->query("SELECT c.*, 
                            (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count 
                            FROM categories c ORDER BY c.name ASC");
?>

<h1>Categories</h1>

<?php if ($message): ?><div class="alert alert-success"><?php echo clean($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo clean($error); ?></div><?php endif; ?>

<div class="form-card">
    <h2><?php echo $editing_category ? 'Edit Category' : 'Add New Category'; ?></h2>
    <form method="POST" action="categories.php">
        <?php if ($editing_category): ?>
            <input type="hidden" name="category_id" value="<?php echo $editing_category['id']; ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="name" value="<?php echo $editing_category ? clean($editing_category['name']) : ''; ?>" required>
            </div>
            <div class="form-group form-group-full">
                <label>Description</label>
                <input type="text" name="description" value="<?php echo $editing_category ? clean($editing_category['description']) : ''; ?>">
            </div>
        </div>
        <?php if ($editing_category): ?>
            <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
            <a href="/kinmel/admin/categories.php" class="btn btn-secondary-light">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
        <?php endif; ?>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Products</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($categories->num_rows > 0): ?>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?php echo clean($cat['name']); ?></td>
                    <td><?php echo clean($cat['description']); ?></td>
                    <td><?php echo (int)$cat['product_count']; ?></td>
                    <td>
                        <a href="/kinmel/admin/categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-small btn-secondary-light">Edit</a>
                        <form method="POST" action="categories.php" style="display:inline;" onsubmit="return confirm('Delete this category? This will also delete its products.');">
                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                            <button type="submit" name="delete_category" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No categories yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
