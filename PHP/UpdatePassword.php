<?php
session_start();
include "Config.php";

if (!isset($_SESSION["inhabitants_id"])) {
    echo "UNAUTHORIZED";
    exit;
}

$user_id = $_SESSION["inhabitants_id"];
$currentPass = $_POST["currentPass"];
$newPass = $_POST["newPass"];

// Fetch current password
$stmt = $conn->prepare("SELECT password FROM inhabitants WHERE inhabitantId = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($currentPass !== $result["password"]) {
    echo "WRONG_PASSWORD";
    exit;
}

// Update password + timestamp
$stmt = $conn->prepare("UPDATE inhabitants SET password = ?, lastPasswordChange = NOW() WHERE inhabitantId = ?");
$stmt->bind_param("si", $newPass, $user_id);

echo ($stmt->execute()) ? "SUCCESS" : "FAILED";
