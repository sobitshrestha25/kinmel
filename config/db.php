<?php
/*
    Database Connection File
    -------------------------
    This file connects our PHP project to the MySQL database
    "kinmel_db" using mysqli (procedural style is avoided here,
    we use the object-oriented style which is still simple).
*/

$db_host = "localhost";
$db_user = "root";      // default XAMPP username
$db_pass = "";           // default XAMPP password is empty
$db_name = "kinmel_db";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character set to avoid encoding issues
$conn->set_charset("utf8mb4");
?>
