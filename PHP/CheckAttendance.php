<?php
header("Content-Type: application/json");
session_start();
include "Config.php";

if (!isset($_SESSION["nik"])) {
    echo json_encode(["status" => "error", "message" => "Session NIK hilang"]);
    exit;
}

$nik = $_SESSION["nik"];
$now = date("H:i:s");

$sql = "
SELECT 
    a.attendanceId,
    a.attendanceStatus,
    req.timeStart,
    req.timeEnd
FROM attendances a
JOIN requirements req
WHERE a.nik = ?
AND (
        (req.timeStart <= req.timeEnd AND ? BETWEEN req.timeStart AND req.timeEnd)
     OR (req.timeStart >  req.timeEnd AND (? >= req.timeStart OR ? <= req.timeEnd))
    )
LIMIT 1
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => $conn->error]);
    exit;
}

$stmt->bind_param("ssss", $nik, $now, $now, $now);

$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode([
        "status" => "closed",
        "message" => "Absensi hanya bisa dilakukan pada jam ronda"
    ]);
    exit;
}

$data = $res->fetch_assoc();

echo json_encode([
    "status" => "open",
    "attendanceId" => $data["attendanceId"],
    "attendanceStatus" => $data["attendanceStatus"]
]);
