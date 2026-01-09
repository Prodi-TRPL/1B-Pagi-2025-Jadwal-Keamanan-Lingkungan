<?php
session_start();
include "Config.php";

if (!isset($_SESSION["admin_id"])) {
    echo "UNAUTHORIZED";
    exit;
}

$user_id = $_SESSION["admin_id"];
$newEmail = $_POST["email"];

// Validate email
if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    echo "INVALID_EMAIL";
    exit;
}

// Update email + timestamp
$query = $conn->prepare(
    "UPDATE admin
     SET email = ?, lastEmailChange = NOW() 
     WHERE adminId = ?"
);

$query->bind_param("si", $newEmail, $user_id);

if ($query->execute()) {
    echo "SUCCESS";
} else {
    echo "FAILED";
}
