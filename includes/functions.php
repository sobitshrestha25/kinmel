<?php
/*
    Common Helper Functions
    -------------------------
    Small reusable functions used across the project.
    session_start() is called here so every page that includes
    this file automatically has session access.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if a user is logged in (any role)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if the logged-in user is an admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Where to send a logged-in user, based on their role
// (admin -> admin dashboard, customer -> customer home page)
function dashboardUrl() {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return "/kinmel/admin/index.php";
    }
    return "/kinmel/home.php";
}

// Protect customer pages - only logged in customers/admins can view
function requireLogin() {
    if (!isLoggedIn()) {
        redirect("/kinmel/login.php");
    }
}

// Protect admin pages - only admin role can view
function requireAdmin() {
    if (!isAdmin()) {
        redirect("/kinmel/login.php");
    }
}

// Clean user input to prevent XSS when displaying data
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>