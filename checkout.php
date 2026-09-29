<?php
// checkout.php - validates cart & stock, then creates the order from the cart
require 'includes/db.php';
require 'includes/functions.php';
requireLogin();

$userId = (int) $_SESSION['user_id'];
$error = "";

// Load the current cart with product details
$stmt = $conn->prepare("SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, p.stock
                         FROM cart c JOIN products p ON c.product_id = p.product_id
                         WHERE c.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (count($cartItems) === 0) {
    header("Location: cart.php");
    exit();
}

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

if (isset($_POST['place_order'])) {
    // 6.1 Validate Cart & Stock
    $insufficient = [];
    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            $insufficient[] = $item['name'];
        }
    }

    if (!empty($insufficient)) {
        $error = "Insufficient stock for: " . implode(", ", $insufficient);
    } else {
        // 6.2 Create Order
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("id", $userId, $total);
            $stmt->execute();
            $orderId = $conn->insert_id;

            foreach ($cartItems as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal)
                                         VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiidd", $orderId, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
                $stmt->execute();

                // reduce stock
                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
                $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmt->execute();
            }

            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $conn->commit();
            header("Location: order_success.php?order_id=" . $orderId);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Something went wrong while placing your order. Please try again.";
        }
    }
}

$pageTitle = "Checkout";
require 'includes/header.php';
?>
<h1 style="font-size:20px; margin-bottom:16px;">Checkout</h1>

<?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

<div class="card-box">
    <h2 class="section-title">Order Summary</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Product</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($cartItems as $item): ?>
            <tr>
                <td><?php echo sanitize($item['name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="cart-summary">
    <div class="row total-row"><span>Total Payable</span><span><?php echo formatPrice($total); ?></span></div>
    <form method="POST">
        <button type="submit" name="place_order" class="btn-primary" style="margin-top:14px;">Place Order (Cash on Delivery)</button>
    </form>
</div>
<?php require 'includes/footer.php'; ?>
