<?php
header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);
session_start();
include "Config.php";

if (!isset($_SESSION['rw'])) {
    echo json_encode(["status"=>"error","message"=>"Session RW tidak ditemukan"]);
    exit;
}

$rw = $_SESSION['rw'];

$stmt = $conn->prepare("SELECT latitude, longitude, radius FROM radius WHERE rw=? LIMIT 1");
$stmt->bind_param("s", $rw);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status"=>"error","message"=>"Lokasi RW belum disetting"]);
    exit;
}

$r = $res->fetch_assoc();

$lat = floatval($r['latitude']);
$lng = floatval($r['longitude']);
$rad = floatval($r['radius']);

if ($lat == 0 || $lng == 0) {
    echo json_encode(["status"=>"error","message"=>"Koordinat RW tidak valid"]);
    exit;
}

echo json_encode([
    "status"=>"ok",
    "latitude"=>$lat,
    "longitude"=>$lng,
    "radius"=>$rad
]);
