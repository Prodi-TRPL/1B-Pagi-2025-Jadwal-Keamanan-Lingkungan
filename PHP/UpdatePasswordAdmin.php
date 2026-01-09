<?php
session_start();
include "Config.php";

if (!isset($_SESSION["admin_id"])) {
    echo "UNAUTHORIZED";
    exit;
}

$user_id = $_SESSION["admin_id"];
$currentPass = $_POST["currentPass"];
$newPass = $_POST["newPass"];

// Fetch current password
$stmt = $conn->prepare("SELECT password FROM admin WHERE adminId = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($currentPass !== $result["password"]) {
    echo "WRONG_PASSWORD";
    exit;
}

// Update password + timestamp
$stmt = $conn->prepare("UPDATE admin SET password = ?, lastPasswordChange = NOW() WHERE adminId = ?");
$stmt->bind_param("si", $newPass, $user_id);

echo ($stmt->execute()) ? "SUCCESS" : "FAILED";
