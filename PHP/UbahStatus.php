<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include "Config.php";

if (!isset($_POST['status']) || !isset($_SESSION['rw'])) {
    echo "error";
    exit();
}

$status = $_POST['status'];
$rw = $_SESSION['rw'];
$now = date("Y-m-d H:i:s");
$today = date("Y-m-d");

/* CEK apakah hari ini sudah ada */
$cek = $conn->prepare("
    SELECT statusId
    FROM envistatus
    WHERE rw = ?
      AND DATE(date) = ?
    LIMIT 1
");
$cek->bind_param("is", $rw, $today);
$cek->execute();
$r = $cek->get_result();

/* SUDAH ADA → UPDATE */
if ($r->num_rows > 0) {

    $row = $r->fetch_assoc();

    $upd = $conn->prepare("
        UPDATE envistatus
        SET status = ?, date = ?
        WHERE statusId = ?
    ");
    $upd->bind_param("ssi", $status, $now, $row['statusId']);
    echo $upd->execute() ? "success" : "error";
}

/* BELUM ADA → INSERT */
else {

    $ins = $conn->prepare("
        INSERT INTO envistatus (status, date, rw)
        VALUES (?, ?, ?)
    ");
    $ins->bind_param("ssi", $status, $now, $rw);
    echo $ins->execute() ? "success" : "error";
}
