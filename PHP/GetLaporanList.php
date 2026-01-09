<?php
session_start();
include "../PHP/Config.php";

header("Content-Type: application/json");
error_reporting(0);

if (!isset($_SESSION['rw'])) {
    echo json_encode(["error" => "Session RW tidak ditemukan"]);
    exit();
}

$rw = $_SESSION['rw'];

/* ===========================================
   1. REPORTS
=========================================== */
$sql = "SELECT * FROM reports WHERE rw='$rw' ORDER BY time DESC";
$res = $conn->query($sql);

if (!$res) {
    echo json_encode([
        "error" => "Query gagal (reports)",
        "detail" => $conn->error,
        "sql" => $sql
    ]);
    exit();
}

$reports = [];
while ($row = $res->fetch_assoc()) {
    $reports[] = $row;
}

/* ===========================================
   2. SUBMISSIONS
=========================================== */
$sql2 = "SELECT * FROM submission WHERE rw='$rw' ORDER BY date DESC";
$res2 = $conn->query($sql2);

if (!$res2) {
    echo json_encode([
        "error" => "Query gagal (submissions)",
        "detail" => $conn->error,
        "sql" => $sql2
    ]);
    exit();
}

$submissions = [];
while ($row = $res2->fetch_assoc()) {
    $submissions[] = $row;
}

/* ===========================================
   3. OUTPUT JSON
=========================================== */
echo json_encode([
    "reports" => $reports,
    "submissions" => $submissions
]);
