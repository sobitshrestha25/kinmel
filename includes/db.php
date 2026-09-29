<?php
// includes/db.php - single MySQL connection reused by every page

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "kinmel";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
