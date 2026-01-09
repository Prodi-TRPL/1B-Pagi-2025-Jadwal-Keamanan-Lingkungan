<?php
session_start();
include "Config.php";

if (!isset($_SESSION["rw"])) {
    echo json_encode(["status" => "error", "message" => "RW tidak ditemukan"]);
    exit;
}

$rw = $_SESSION["rw"];
$today = date("Y-m-d");

// Ambil rute yang dipakai berdasarkan attendance hari ini
$sql = "
    SELECT DISTINCT r.routeId, r.routeName
    FROM attendances a
    JOIN inhabitants i ON a.nik = i.nik
    JOIN route r ON a.routeId = r.routeId
    WHERE r.rw = ?
      AND DATE(a.date) = ?
    ORDER BY r.routeName ASC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => $conn->error]);
    exit;
}

$stmt->bind_param("is", $rw, $today);
$stmt->execute();
$result = $stmt->get_result();

$routes = [];
while ($row = $result->fetch_assoc()) {
    $routes[] = [
        "id" => $row["routeId"],
        "name" => $row["routeName"]
    ];
}

echo json_encode(["status" => "success", "routes" => $routes]);
