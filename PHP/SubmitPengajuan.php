<?php
header("Content-Type: application/json");
session_start();
include "Config.php";

if (!isset($_SESSION["nik"]) || !isset($_SESSION["rw"])) {
    echo json_encode(["status" => "error", "message" => "Session tidak ditemukan"]);
    exit;
}

$nik = $_SESSION["nik"];
$rw = $_SESSION["rw"];

$type = $_POST["type"] ?? "";
$description = $_POST["description"] ?? "";

if ($type == "" || $description == "") {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

$sql = "INSERT INTO submission (nik, type, date, description, submissionStatus, rw)
        VALUES (?, ?, NOW(), ?, 'Pending', ?)";

$query = $conn->prepare($sql);
$query->bind_param("sssi", $nik, $type, $description, $rw);

if ($query->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
