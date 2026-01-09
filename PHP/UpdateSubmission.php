<?php
session_start();
include "Config.php";

if (!isset($_SESSION['inhabitants_id'])) {
  echo "unauthorized";
  exit();
}

if (!isset($_POST['id'], $_POST['action'])) {
  echo "invalid";
  exit();
}

$id     = intval($_POST['id']);
$status = $_POST['action'];

$stmt = $conn->prepare("UPDATE submission SET submissionStatus=? WHERE submissionId=?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
  echo "success";
} else {
  echo "error";
}
