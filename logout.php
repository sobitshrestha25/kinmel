<?php
require_once __DIR__ . '/includes/functions.php';
$_SESSION = [];
session_destroy();
header("Location: /kinmel/login.php");
exit();
?>
