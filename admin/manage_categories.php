<?php
// admin/manage_categories.php - list, add, and delete product categories
require '../includes/db.php';
require '../includes/functions.php';
requireAdmin();

$error = "";

if (isset($_POST['add_category'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);

    if ($name === "") {
        $error = "Category name is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
    }
}

$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) AS product_count
                             FROM categories c ORDER BY c.name");

$pageTitle = "Manage Categories";
require '../includes/admin_header.php';
?>
<h1 class="section-title">Manage Categories</h1>

<?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

<div class="card-box" style="max-width:500px;">
    <h2 class="section-title" style="font-size:15px;">Add New Category</h2>
    <form method="POST">
        <div class="form-group">
            <label>Category Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="2"></textarea>
        </div>
        <button type="submit" name="add_category" class="btn-primary">Add Category</button>
    </form>
</div>

<div class="card-box">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Name</th><th>Description</th><th>Products</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($c = $categories->fetch_assoc()): ?>
            <tr>
                <td><?php echo sanitize($c['name']); ?></td>
                <td><?php echo sanitize($c['description']); ?></td>
                <td><?php echo $c['product_count']; ?></td>
                <td>
                    <a href="delete_category.php?id=<?php echo $c['category_id']; ?>" class="btn btn-red" style="font-size:12px;"
                       onclick="return confirm('Delete this category? Its products will also be removed.');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require '../includes/admin_footer.php'; ?>
