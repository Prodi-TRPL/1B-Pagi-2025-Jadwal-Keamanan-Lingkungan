<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tree_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// ✔ Set PHP timezone
date_default_timezone_set('Asia/Jakarta');

// ✔ Force MySQL to use same timezone
mysqli_query($conn, "SET time_zone = '+07:00'");
?>
