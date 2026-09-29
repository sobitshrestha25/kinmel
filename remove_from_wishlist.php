<?php
// remove_from_wishlist.php - delete a saved product from the wishlist
require 'includes/db.php';
require 'includes/functions.php';
requireLogin();

$userId = (int) $_SESSION['user_id'];
$wishlistId = (int) ($_GET['wishlist_id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM wishlist WHERE wishlist_id = ? AND user_id = ?");
$stmt->bind_param("ii", $wishlistId, $userId);
$stmt->execute();

header("Location: wishlist.php");
exit();
