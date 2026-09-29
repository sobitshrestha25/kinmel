<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
if (isAdmin()) redirect('/kinmel/admin/index.php');

$user_id = $_SESSION['user_id'];
$message = "";

// ---------------- Update quantity ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $cart_id = (int)$_POST['cart_id'];
    $action = $_POST['action']; // 'increase' or 'decrease'

    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $qty = $row['quantity'];

        if ($action === 'increase') $qty++;
        if ($action === 'decrease') $qty--;

        if ($qty <= 0) {
            $del = $conn->prepare("DELETE FROM cart WHERE id = ?");
            $del->bind_param("i", $cart_id);
            $del->execute();
        } else {
            $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $upd->bind_param("ii", $qty, $cart_id);
            $upd->execute();
        }
    }
}

// ---------------- Remove item ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $cart_id = (int)$_POST['cart_id'];
    $del = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $del->bind_param("ii", $cart_id, $user_id);
    $del->execute();
}

// ---------------- Checkout ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {

    // Get the customer's address for shipping
    $userStmt = $conn->prepare("SELECT address FROM users WHERE id = ?");
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $userRow = $userStmt->get_result()->fetch_assoc();
    $shipping_address = $userRow['address'];

    // Get cart items with current product prices and stock
    $cartStmt = $conn->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.stock 
                                 FROM cart c JOIN products p ON c.product_id = p.id 
                                 WHERE c.user_id = ?");
    $cartStmt->bind_param("i", $user_id);
    $cartStmt->execute();
    $cartItems = $cartStmt->get_result();

    $items = [];
    $total = 0;
    $stockProblem = false;

    while ($item = $cartItems->fetch_assoc()) {
        if ($item['quantity'] > $item['stock']) {
            $stockProblem = true;
        }
        $items[] = $item;
        $total += $item['price'] * $item['quantity'];
    }

    if (empty($items)) {
        $message = "Your cart is empty.";
    } elseif ($stockProblem) {
        $message = "Some items in your cart exceed available stock. Please adjust quantities.";
    } else {
        // Create the order
        $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, 'Pending', ?)");
        $orderStmt->bind_param("ids", $user_id, $total, $shipping_address);
        $orderStmt->execute();
        $order_id = $conn->insert_id;

        foreach ($items as $item) {
            // Insert order item
            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            $itemStmt->bind_param("iisdi", $order_id, $item['product_id'], $item['name'], $item['price'], $item['quantity']);
            $itemStmt->execute();

            // Reduce stock
            $newStock = $item['stock'] - $item['quantity'];
            $stockStmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stockStmt->bind_param("ii", $newStock, $item['product_id']);
            $stockStmt->execute();
        }

        // Clear cart
        $clearStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearStmt->bind_param("i", $user_id);
        $clearStmt->execute();

        redirect('/kinmel/my_orders.php?success=1');
    }
}

// ---------------- Fetch current cart for display ----------------
$stmt = $conn->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.image, p.stock 
                         FROM cart c JOIN products p ON c.product_id = p.id 
                         WHERE c.user_id = ?
                         ORDER BY c.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cartItems = $stmt->get_result();

$grandTotal = 0;
$cartRows = [];
while ($row = $cartItems->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $grandTotal += $row['subtotal'];
    $cartRows[] = $row;
}

require_once __DIR__ . '/includes/header.php';
?>

<h1>My Cart</h1>

<?php if ($message): ?>
    <div class="alert alert-error"><?php echo clean($message); ?></div>
<?php endif; ?>

<?php if (count($cartRows) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartRows as $row): ?>
                <tr>
                    <td>
                        <img src="/kinmel/uploads/products/<?php echo clean($row['image']); ?>"
                             onerror="this.src='/kinmel/images/no-image.png'"
                             alt="<?php echo clean($row['name']); ?>" class="cart-thumb">
                        <?php echo clean($row['name']); ?>
                    </td>
                    <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                    <td>
                        <div class="qty-controls">
                            <form method="POST" action="cart.php" class="qty-form">
                                <input type="hidden" name="cart_id" value="<?php echo $row['cart_id']; ?>">
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" name="update_qty" value="1" class="qty-btn">-</button>
                            </form>
                            <span class="qty-number"><?php echo $row['quantity']; ?></span>
                            <form method="POST" action="cart.php" class="qty-form">
                                <input type="hidden" name="cart_id" value="<?php echo $row['cart_id']; ?>">
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" name="update_qty" value="1" class="qty-btn">+</button>
                            </form>
                        </div>
                    </td>
                    <td>Rs. <?php echo number_format($row['subtotal'], 2); ?></td>
                    <td>
                        <form method="POST" action="cart.php" style="display:inline;">
                            <input type="hidden" name="cart_id" value="<?php echo $row['cart_id']; ?>">
                            <button type="submit" name="remove_item" class="btn btn-danger btn-small">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="cart-summary">
        <h3>Total: Rs. <?php echo number_format($grandTotal, 2); ?></h3>
        <form method="POST" action="cart.php">
            <button type="submit" name="checkout" class="btn btn-primary">Proceed to Checkout</button>
        </form>
    </div>
<?php else: ?>
    <p>Your cart is empty. <a href="/kinmel/products.php">Browse products</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
