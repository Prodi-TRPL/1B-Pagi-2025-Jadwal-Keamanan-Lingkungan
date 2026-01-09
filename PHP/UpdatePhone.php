<?php
session_start();
include "Config.php";

if (!isset($_SESSION["inhabitants_id"])) {
    echo "UNAUTHORIZED";
    exit;
}

$user_id = $_SESSION["inhabitants_id"];
$newPhone = $_POST["phone"];

// Validate phone format (Indonesia format allowed: +62 or 08)
if (!preg_match("/^(?:\+62|08)[0-9]{8,13}$/", $newPhone)) {
    echo "INVALID_PHONE";
    exit;
}

// Update phone + timestamp
$query = $conn->prepare("
    UPDATE inhabitants 
    SET phone = ?, lastPhoneChange = NOW() 
    WHERE inhabitantId = ?
");
$query->bind_param("si", $newPhone, $user_id);

if ($query->execute()) {
    echo "SUCCESS";
} else {
    echo "FAILED";
}
