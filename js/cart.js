// js/cart.js - adding/updating cart and wishlist items without a full page reload

function addToCart(productId) {
    fetch("add_to_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "product_id=" + productId + "&quantity=1"
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            const badge = document.querySelector('a[href="cart.php"]');
            if (badge) badge.textContent = "Cart (" + data.total_items + ")";
        } else {
            alert(data.message || "Could not add product to cart.");
        }
    });
}

function updateCartQuantity(cartId, change) {
    fetch("update_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "cart_id=" + cartId + "&change=" + change
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            window.location.reload();
        } else {
            alert(data.message || "Could not update cart.");
        }
    });
}

function addToWishlist(productId) {
    fetch("add_to_wishlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "product_id=" + productId
    })
    .then(res => res.json())
    .then(data => alert(data.message));
}
