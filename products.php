<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if (isAdmin()) {
    redirect('/kinmel/admin/index.php');
}

$message = "";

// ---------------- Handle "Add to Cart" ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    $product_id = (int)$_POST['product_id'];
    $user_id = $_SESSION['user_id'];

    // Check product exists and has stock
    $check = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $check->bind_param("i", $product_id);
    $check->execute();

    $res = $check->get_result();

    if ($res->num_rows === 1) {

        $product = $res->fetch_assoc();

        if ($product['stock'] > 0) {

            // Check if product already exists in cart
            $find = $conn->prepare(
                "SELECT id, quantity 
                 FROM cart 
                 WHERE user_id = ? AND product_id = ?"
            );

            $find->bind_param("ii", $user_id, $product_id);
            $find->execute();

            $cartResult = $find->get_result();

            if ($cartResult->num_rows === 1) {

                $cartRow = $cartResult->fetch_assoc();

                $newQty = $cartRow['quantity'] + 1;

                $update = $conn->prepare(
                    "UPDATE cart SET quantity = ? WHERE id = ?"
                );

                $update->bind_param(
                    "ii",
                    $newQty,
                    $cartRow['id']
                );

                $update->execute();

            } else {

                $insert = $conn->prepare(
                    "INSERT INTO cart (user_id, product_id, quantity)
                     VALUES (?, ?, 1)"
                );

                $insert->bind_param(
                    "ii",
                    $user_id,
                    $product_id
                );

                $insert->execute();
            }

            $message = "Product added to cart.";

        } else {

            $message = "Sorry, this product is out of stock.";
        }
    }

    $check->close();
}


// ---------------- Build product query ----------------

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$category_id = isset($_GET['category_id'])
    ? (int)$_GET['category_id']
    : 0;


$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE 1=1";

$params = [];
$types = "";


if ($search !== '') {

    $sql .= " AND p.name LIKE ?";

    $params[] = "%$search%";

    $types .= "s";
}


if ($category_id > 0) {

    $sql .= " AND p.category_id = ?";

    $params[] = $category_id;

    $types .= "i";
}


$sql .= " ORDER BY p.created_at DESC";


$stmt = $conn->prepare($sql);


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


$stmt->execute();

$products = $stmt->get_result();


// ---------------- Categories ----------------

$categories = $conn->query(
    "SELECT id, name 
     FROM categories 
     ORDER BY name ASC"
);


require_once __DIR__ . '/includes/header.php';
?>


<h1>Our Products</h1>


<?php if ($message): ?>

    <div class="alert alert-success">
        <?php echo clean($message); ?>
    </div>

<?php endif; ?>


<!-- Search and Filter -->

<form method="GET"
      action="products.php"
      class="search-filter-bar">

    <input
        type="text"
        name="search"
        placeholder="Search products..."
        value="<?php echo clean($search); ?>"
    >

    <select name="category_id">

        <option value="0">
            All Categories
        </option>

        <?php while ($cat = $categories->fetch_assoc()): ?>

            <option
                value="<?php echo $cat['id']; ?>"
                <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>
            >
                <?php echo clean($cat['name']); ?>
            </option>

        <?php endwhile; ?>

    </select>

    <button
        type="submit"
        class="btn btn-primary btn-small">
        Search
    </button>

</form>


<!-- Products -->

<div class="product-grid">

    <?php if ($products->num_rows > 0): ?>

        <?php while ($p = $products->fetch_assoc()): ?>

            <div class="product-card">

                <!-- Wishlist Heart -->

                <button
                    type="button"
                    class="wishlist-btn"
                    onclick="addToWishlist(<?php echo $p['id']; ?>, this)"
                    title="Add to Wishlist">
                    ♡
                </button>


                <!-- Product Image -->

                <img
                    src="/kinmel/uploads/products/<?php echo clean($p['image']); ?>"
                    onerror="this.src='/kinmel/images/no-image.png'"
                    alt="<?php echo clean($p['name']); ?>"
                >


                <div class="product-card-body">

                    <!-- Product Name -->

                    <h3>
                        <?php echo clean($p['name']); ?>
                    </h3>


                    <!-- Category -->

                    <div class="product-category">
                        <?php echo clean($p['category_name']); ?>
                    </div>


                    <!-- Price -->

                    <div class="product-price">
                        Rs. <?php echo number_format($p['price'], 2); ?>
                    </div>


                    <!-- Stock -->

                    <?php if ($p['stock'] > 0): ?>

                        <span class="stock-badge in-stock">
                            In Stock
                            (<?php echo (int)$p['stock']; ?>)
                        </span>

                    <?php else: ?>

                        <span class="stock-badge out-of-stock">
                            Out of Stock
                        </span>

                    <?php endif; ?>


                    <!-- Add to Cart -->

                    <form
                        method="POST"
                        action="products.php">

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?php echo $p['id']; ?>"
                        >

                        <button
                            type="submit"
                            name="add_to_cart"
                            class="btn btn-primary btn-block btn-small"
                            <?php echo $p['stock'] <= 0 ? 'disabled' : ''; ?>
                        >
                            Add to Cart
                        </button>

                    </form>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No products found.</p>

    <?php endif; ?>

</div>


<!-- Wishlist JavaScript -->

<script>

function addToWishlist(productId, button) {

    fetch('add_to_wishlist.php', {

        method: 'POST',

        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },

        body: 'product_id=' + productId

    })

    .then(response => response.json())

    .then(data => {

        alert(data.message);

        if (data.status === 'success') {

            button.innerHTML = '♥';

            button.classList.add('active');

        }

    })

    .catch(error => {

        console.error(error);

        alert('Something went wrong.');

    });

}

</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>